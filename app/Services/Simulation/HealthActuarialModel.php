<?php

namespace App\Services\Simulation;

use Illuminate\Support\Collection;

class HealthActuarialModel
{
    /**
     * Calculate health trajectory vector (age 0 to 120) and summary actuarial metrics.
     *
     * @param float $currentAge
     * @param float $baselineLifeExpectancy
     * @param Collection $activities
     * @return array
     */
    public function calculate(float $currentAge, float $baselineLifeExpectancy, Collection $activities): array
    {
        // 1. Calculate delta modifiers from activities
        $exerciseBonusYears = 0.0;
        $smokingPenaltyYears = 0.0;
        $bioAgeDelta = 0.0;
        $cardiovascularRiskInflectionAge = null;
        $smokingPackYears = 0.0;

        foreach ($activities as $act) {
            $type = $act->activity_type ?? '';
            $durationYears = max(0.5, ($act->duration_months ?? 12) / 12);
            $isActive = $act->is_active ?? true;

            if ($type === 'workout' || $type === 'daily_exercise') {
                $freq = $act->frequency === 'daily' ? 1.0 : ($act->frequency === 'weekly' ? 0.6 : 0.3);
                $bonus = min(6.5, $durationYears * 0.8 * $freq);
                $exerciseBonusYears += $bonus;
                $bioAgeDelta -= min(5.0, $bonus * 0.75);
            } elseif ($type === 'smoking' || $type === 'cigarettes') {
                $intensity = $act->intensity_or_amount > 0 ? $act->intensity_or_amount : 10; // cigs per day
                $packYears = ($intensity / 20) * $durationYears;
                $smokingPackYears += $packYears;
                $penalty = min(14.0, $packYears * 0.6 + ($isActive ? 2.5 : 0));
                $smokingPenaltyYears += $penalty;
                $bioAgeDelta += min(8.0, $penalty * 0.7);

                if ($packYears >= 5 || ($isActive && $intensity >= 10)) {
                    $cardiovascularRiskInflectionAge = round(max($currentAge + 2, 48.0 + (10 - min(10, $packYears))));
                }
            } elseif ($type === 'sleep_optimization') {
                $exerciseBonusYears += 1.2;
                $bioAgeDelta -= 1.5;
            }
        }

        // Net projected lifespan
        $projectedLifespan = max(45.0, min(105.0, $baselineLifeExpectancy + $exerciseBonusYears - $smokingPenaltyYears));
        $biologicalAge = max(18.0, $currentAge + $bioAgeDelta);

        // 2. Generate age 0 to 120 health score curve (0 - 100)
        $curve = [];
        for ($age = 0; $age <= 120; $age++) {
            $score = $this->calculateHealthScoreAtAge($age, $currentAge, $projectedLifespan, $exerciseBonusYears, $smokingPenaltyYears);
            $curve[$age] = $score;
        }

        return [
            'baseline_life_expectancy' => $baselineLifeExpectancy,
            'projected_lifespan' => round($projectedLifespan, 1),
            'biological_age' => round($biologicalAge, 1),
            'bio_age_delta' => round($bioAgeDelta, 1),
            'exercise_bonus_years' => round($exerciseBonusYears, 1),
            'smoking_penalty_years' => round($smokingPenaltyYears, 1),
            'cardiovascular_risk_age' => $cardiovascularRiskInflectionAge,
            'curve' => $curve,
        ];
    }

    private function calculateHealthScoreAtAge(int $age, float $currentAge, float $lifespan, float $exerciseBonus, float $smokingPenalty): float
    {
        if ($age > $lifespan + 2) {
            return 0.0;
        }

        if ($age < 20) {
            // Childhood to young adult vitality build
            return min(100, round(65 + ($age / 20) * 35, 1));
        }

        // Peak vitality between age 20 and 32
        if ($age <= 32) {
            $base = 98.0;
            if ($exerciseBonus > 0) $base = 100.0;
            if ($smokingPenalty > 3) $base -= ($smokingPenalty * 0.8);
            return max(0, round($base, 1));
        }

        // Midlife & Senior aging trajectory
        $yearsPastPeak = $age - 32;
        $totalSpan = max(10, $lifespan - 32);
        $fraction = min(1.0, $yearsPastPeak / $totalSpan);

        // Gompertz-shaped biological decay
        $decayRate = 2.2;
        if ($smokingPenalty > 0) {
            $decayRate += 0.8; // accelerated decline
        }
        if ($exerciseBonus > 2) {
            $decayRate -= 0.5; // preserved vitality
        }

        $healthPct = 98.0 * (1.0 - pow($fraction, $decayRate));

        if ($age >= $lifespan) {
            $healthPct = max(0, 15.0 - (($age - $lifespan) * 7.5));
        }

        return max(0.0, min(100.0, round($healthPct, 1)));
    }
}
