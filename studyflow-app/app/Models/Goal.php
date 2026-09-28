<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'external_id',
        'title',
        'description',
        'progress',
        'target',
        'current_progress',
        'deadline',
        'category',
        'status',
    ];

    protected $casts = [
        'progress' => 'integer',
        'target' => 'integer',
        'current_progress' => 'integer',
        'deadline' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function milestones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoalMilestone::class);
    }
}
