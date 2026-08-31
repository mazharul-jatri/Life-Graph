<?php

namespace App\Models;

use App\Enums\EventCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LifeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'timeline_id',
        'title',
        'description',
        'category',
        'pillar',
        'event_date',
        'age_at_event',
        'is_projected',
        'impact_score',
        'risk_alert',
        'metadata',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => EventCategory::class,
            'event_date' => 'date:Y-m-d',
            'age_at_event' => 'float',
            'is_projected' => 'boolean',
            'impact_score' => 'integer',
            'metadata' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function timeline(): BelongsTo
    {
        return $this->belongsTo(LifeTimeline::class, 'timeline_id');
    }

    public function decisionPoint(): HasOne
    {
        return $this->hasOne(DecisionPoint::class, 'event_id');
    }
}
