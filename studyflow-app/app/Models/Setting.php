<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'theme',
        'default_study_duration',
        'notifications_enabled',
        'sound_enabled',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'sound_enabled' => 'boolean',
        'default_study_duration' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
