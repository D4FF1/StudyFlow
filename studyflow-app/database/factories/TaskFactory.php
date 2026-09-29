<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'completed']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'importance' => $this->faker->randomElement(['low', 'medium', 'high']),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'deadline' => $this->faker->optional()->dateTimeBetween('now', '+2 weeks'),
            'estimated_minutes' => $this->faker->numberBetween(15, 180),
            'progress' => $this->faker->numberBetween(0, 100),
            'notes' => $this->faker->optional()->sentence(),
            'completed_at' => null,
            'is_overdue' => false,
        ];
    }
}
