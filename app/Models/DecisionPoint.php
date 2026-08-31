<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'prompt',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(LifeEvent::class, 'event_id');
    }
}
