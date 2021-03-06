<?php

namespace Tests\Feature\Admin\Entries;

use App\Entry;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\WrongUpdateEntryDataProvider;
use Tests\TestCase;

class UpdateEntryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WrongUpdateEntryDataProvider;

    /** @test */
    public function anAuthorizedUserCanUpdateAnEntry()
    {
        $user = factory(User::class)->create();
        $entry = factory(Entry::class)->create(['created_by' => $user->id]);

        $entry_data = [
            'title' => $this->faker->text(50),
            'description' => $entry->description,
            'content' => $entry->content,
        ];

        $response = $this->actingAs($user)->put(route('admin.entries.update', $entry->id), $entry_data);

        $entry->title = $entry_data['title'];

        $this->assertDatabaseHas(
            'entries',
            $entry->toArray()
        );

        $response->assertSessionHas('success');
    }

    /** @test */
    public function anUnauthorizedUserCannotUpdateAnEntry()
    {
        $user = factory(User::class)->create();
        $entry = factory(Entry::class)->create(['created_by' => $user->id]);

        $response = $this->put(route('admin.entries.update', $entry->id), []);

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     * @dataProvider wrongEntryData
     * @param callable $field
     * @param string $fieldName
     */
    public function anEntryCannotBeUpdatedDueToValidationErrors(callable $field, string $fieldName)
    {
        $user = factory(User::class)->create();
        $entry = factory(Entry::class)->create(['created_by' => $user->id]);

        $entry_data = [
            'title' => $this->faker->text(50),
            'description' => $entry->description,
            'content' => $entry->content,
        ];

        $entry_data = array_replace_recursive($entry_data, [$fieldName => $field($this->faker)]);

        $response = $this->actingAs($user)->put(route('admin.entries.update', $entry->id), $entry_data);

        $response->assertSessionHasErrors($fieldName);
    }
}
