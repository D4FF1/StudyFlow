<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyFlowAuthAndIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_cannot_access_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Owner task',
        ]);

        $this->actingAs($other)
            ->get('/tasks/'.$task->id)
            ->assertStatus(403);
    }
}
