<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Carbon\Carbon;

class AchievementService
{
    public function syncForUser(User $user): void
    {
        $unlocks = [];

        if ($user->studySessions()->count() >= 1) {
            $unlocks[] = 'first-focus';
        }

        if ($user->tasks()->whereNotNull('completed_at')->count() >= 5) {
            $unlocks[] = 'consistent';
        }

        if ($user->studySessions()->where('duration_minutes', '>=', 120)->count() >= 1) {
            $unlocks[] = 'deep-worker';
        }

        if ($user->goals()->count() >= 3) {
            $unlocks[] = 'goal-crusher';
        }

        if ($user->studySessions()->sum('duration_minutes') >= 300) {
            $unlocks[] = 'bookworm';
        }

        if ($user->tasks()->whereNotNull('completed_at')->count() >= 10) {
            $unlocks[] = 'momentum';
        }

        foreach ($unlocks as $slug) {
            $achievement = Achievement::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => ucfirst(str_replace('-', ' ', $slug)),
                    'description' => 'Unlocked through real StudyFlow activity.',
                    'points' => 10,
                ]
            );

            $alreadyUnlocked = $user->userAchievements()->where('achievement_id', $achievement->id)->exists();
            if (! $alreadyUnlocked) {
                $user->userAchievements()->create([
                    'achievement_id' => $achievement->id,
                    'unlocked_at' => Carbon::now(),
                ]);
            }
        }
    }
}
