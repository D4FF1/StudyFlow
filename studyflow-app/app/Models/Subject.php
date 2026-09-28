<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'color',
        'description',
        'icon',
        'study_minutes',
        'external_id',
    ];

    protected $casts = [
        'study_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function studySessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudySession::class);
    }
}
