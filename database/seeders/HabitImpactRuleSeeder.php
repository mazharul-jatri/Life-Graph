<?php

namespace Database\Seeders;

use App\Models\HabitImpactRule;
use Illuminate\Database\Seeder;

class HabitImpactRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'activity_type' => 'workout',
                'display_name' => 'Consistent Strength & Aerobic Exercise',
                'pillar' => 'health',
                'unit' => 'months',
                'delta_life_expectancy' => 3.2,
                'delta_cardiac_risk_pct' => -35.0,
                'delta_career_multiplier' => 1.10,
                'wealth_annual_roi_pct' => 0.0,
                'description' => 'Meta-analyses show 150min/week moderate-to-vigorous exercise increases life expectancy by 3.2 to 4.5 years.',
            ],
            [
                'activity_type' => 'smoking',
                'display_name' => 'Cigarette Smoking (Daily)',
                'pillar' => 'health',
                'unit' => 'pack_years',
                'delta_life_expectancy' => -9.5,
                'delta_cardiac_risk_pct' => 180.0,
                'delta_career_multiplier' => 0.95,
                'wealth_annual_roi_pct' => 0.0,
                'description' => 'Epidemiological consensus: Lifelong smokers lose ~10 years of life expectancy compared to non-smokers.',
            ],
            [
                'activity_type' => 'workshop',
                'display_name' => 'Communication & Leadership Workshop',
                'pillar' => 'career',
                'unit' => 'course',
                'delta_life_expectancy' => 0.0,
                'delta_cardiac_risk_pct' => 0.0,
                'delta_career_multiplier' => 1.22,
                'wealth_annual_roi_pct' => 0.0,
                'description' => 'Soft skill mastery accelerates promotion velocity by ~20% based on career development studies.',
            ],
            [
                'activity_type' => 'education',
                'display_name' => 'Bachelor / Master Degree',
                'pillar' => 'career',
                'unit' => 'degree',
                'delta_life_expectancy' => 1.5,
                'delta_cardiac_risk_pct' => -10.0,
                'delta_career_multiplier' => 1.35,
                'wealth_annual_roi_pct' => 0.0,
                'description' => 'Higher educational attainment correlates with higher lifetime earnings and health literacy.',
            ],
            [
                'activity_type' => 'investment',
                'display_name' => 'Disciplined Index Fund Investing',
                'pillar' => 'wealth',
                'unit' => 'monthly_usd',
                'delta_life_expectancy' => 0.0,
                'delta_cardiac_risk_pct' => -5.0,
                'delta_career_multiplier' => 1.0,
                'wealth_annual_roi_pct' => 7.5,
                'description' => 'Long-term S&P 500 inflation-adjusted compounding yields ~7.5% real annual return over 20+ year horizons.',
            ],
        ];

        foreach ($rules as $rule) {
            HabitImpactRule::updateOrCreate(
                ['activity_type' => $rule['activity_type']],
                $rule
            );
        }
    }
}
