<?php

namespace App\Services;

use App\Models\Task;

class PriorityEngine
{
    public function calculate(Task $task): array
    {
        $score = 0;
        $reasons = [];

        $daysUntilDeadline = $task->deadline ? max(0, $task->deadline->diffInDays(now(), false)) : null;
        if ($daysUntilDeadline !== null) {
            if ($daysUntilDeadline <= 0) {
                $score += 30;
                $reasons[] = 'Deadline is overdue';
            } elseif ($daysUntilDeadline <= 1) {
                $score += 24;
                $reasons[] = 'Deadline is tomorrow';
            } elseif ($daysUntilDeadline <= 3) {
                $score += 16;
                $reasons[] = 'Deadline is coming up soon';
            }
        }

        $importance = match (strtolower((string) $task->importance)) {
            'high' => 25,
            'medium' => 15,
            'low' => 5,
            default => 10,
        };
        $score += $importance;
        if ($importance >= 25) {
            $reasons[] = 'High importance';
        }

        $difficulty = match (strtolower((string) $task->difficulty)) {
            'hard' => 20,
            'medium' => 12,
            'easy' => 8,
            default => 10,
        };
        $score += $difficulty;

        $duration = (int) ($task->estimated_minutes ?? 45);
        if ($duration <= 30) {
            $score += 8;
            $reasons[] = 'Estimated duration is manageable';
        } elseif ($duration <= 60) {
            $score += 5;
        }

        $progress = (int) ($task->progress ?? 0);
        if ($progress < 30) {
            $score += 12;
            $reasons[] = 'Progress is only '. $progress .'%';
        }

        $score = min(100, max(0, $score));

        $level = match (true) {
            $score >= 80 => 'High',
            $score >= 60 => 'Medium',
            default => 'Low',
        };

        return [
            'score' => $score,
            'level' => $level,
            'reasons' => array_values(array_unique($reasons)),
        ];
    }
}
