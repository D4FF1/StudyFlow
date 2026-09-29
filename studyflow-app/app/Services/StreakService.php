<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class StreakService
{
    public function current(User $user): int
    {
        $dates = $this->studyDays($user);
        $current = 0;
        $today = Carbon::now($user->timezone ?? 'UTC')->toDateString();
        $cursor = Carbon::parse($today)->startOfDay();

        while (in_array($cursor->toDateString(), $dates, true)) {
            $current++;
            $cursor->subDay();
        }

        return $current;
    }

    public function longest(User $user): int
    {
        $dates = $this->studyDays($user);
        if (empty($dates)) {
            return 0;
        }

        $dates = array_values(array_unique($dates));
        sort($dates);

        $longest = 1;
        $run = 1;

        for ($i = 1; $i < count($dates); $i++) {
            $prev = Carbon::parse($dates[$i - 1]);
            $current = Carbon::parse($dates[$i]);

            if ($prev->diffInDays($current) === 1) {
                $run++;
                $longest = max($longest, $run);
            } else {
                $run = 1;
            }
        }

        return $longest;
    }

    protected function studyDays(User $user): array
    {
        $timezone = $user->timezone ?? 'UTC';

        $taskDates = $user->tasks()
            ->whereNotNull('completed_at')
            ->selectRaw('DATE(completed_at) as completed_date')
            ->groupBy('completed_date')
            ->pluck('completed_date')
            ->map(fn ($date) => Carbon::parse($date, $timezone)->setTimezone($timezone)->toDateString())
            ->all();

        $sessionDates = $user->studySessions()
            ->whereNotNull('ended_at')
            ->selectRaw('DATE(ended_at) as completed_date')
            ->groupBy('completed_date')
            ->pluck('completed_date')
            ->map(fn ($date) => Carbon::parse($date, $timezone)->setTimezone($timezone)->toDateString())
            ->all();

        return array_values(array_unique(array_merge($taskDates, $sessionDates)));
    }
}
