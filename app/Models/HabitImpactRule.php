<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabitImpactRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_type',
        'display_name',
        'pillar',
        'unit',
        'delta_life_expectancy',
        'delta_cardiac_risk_pct',
        'delta_career_multiplier',
        'wealth_annual_roi_pct',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'delta_life_expectancy' => 'float',
            'delta_cardiac_risk_pct' => 'float',
            'delta_career_multiplier' => 'float',
            'wealth_annual_roi_pct' => 'float',
            'metadata' => 'array',
        ];
    }
}
