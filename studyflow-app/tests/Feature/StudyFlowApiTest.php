<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyFlowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_sync_tasks(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tasks/sync', [
                'items' => [
                    [
                        'id' => 'task_1',
                        'title' => 'Read chapter 3',
                        'status' => 'In Progress',
                        'priority' => 'High',
                        'estimatedMinutes' => 45,
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Read chapter 3',
            'status' => 'In Progress',
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(0);
    }
}
