<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifeActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'timeline_id',
        'title',
        'activity_type',
        'pillar',
        'frequency',
        'intensity_or_amount',
        'start_age',
        'end_age',
        'duration_months',
        'impact_coefficients',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'intensity_or_amount' => 'float',
            'start_age' => 'float',
            'end_age' => 'float',
            'duration_months' => 'integer',
            'impact_coefficients' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeline(): BelongsTo
    {
        return $this->belongsTo(LifeTimeline::class, 'timeline_id');
    }
}
