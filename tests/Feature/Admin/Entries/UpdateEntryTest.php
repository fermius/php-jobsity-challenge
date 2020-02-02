<?php

namespace Tests\Feature\Admin\Entries;

use App\Entry;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateEntryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function anAuthorizedUserCanUpdateAnEntry()
    {
        $user = factory(User::class)->create();
        $entry = factory(Entry::class)->create(['created_by' => $user->id]);

        $entry_data = [
            'title' => $this->faker->realText(20),
        ];

        $this->actingAs($user)->put(route('admin.entries.update', $entry->id), $entry_data);

        $entry->title = $entry_data['title'];

        $this->assertDatabaseHas(
            'entries',
            $entry->toArray(),
        );
    }

    /** @test */
    public function anUnauthorizedUserCannotUpdateAnEntry()
    {
        $user = factory(User::class)->create();
        $entry = factory(Entry::class)->create(['created_by' => $user->id]);

        $response = $this->put(route('admin.entries.update', $entry->id), []);

        $response->assertRedirect(route('login'));
    }
}