<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'external_id',
        'title',
        'description',
        'status',
        'priority',
        'importance',
        'difficulty',
        'external_subject_id',
        'subject_name',
        'deadline',
        'estimated_minutes',
        'progress',
        'notes',
        'completed_at',
        'is_overdue',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'integer',
        'estimated_minutes' => 'integer',
        'is_overdue' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
