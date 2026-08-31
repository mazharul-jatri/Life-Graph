<?php

namespace App\Services\Simulation;

use Illuminate\Support\Collection;

class CareerVelocityModel
{
    /**
     * Calculate career velocity index, skill mastery curve (age 0 to 120), and promotion milestones.
     *
     * @param float $currentAge
     * @param Collection $activities
     * @return array
     */
    public function calculate(float $currentAge, Collection $activities): array
    {
        $hasDegree = false;
        $skillWorkshopCount = 0;
        $velocityMultiplier = 1.0;

        foreach ($activities as $act) {
            $type = $act->activity_type ?? '';
            if ($type === 'education' || $type === 'degree') {
                $hasDegree = true;
                $velocityMultiplier += 0.20;
            } elseif ($type === 'workshop' || $type === 'skill_training') {
                $skillWorkshopCount++;
                $velocityMultiplier += 0.15;
            }
        }

        $curve = [];
        for ($age = 0; $age <= 120; $age++) {
            if ($age < 18) {
                $curve[$age] = 0;
            } elseif ($age <= 22) {
                $curve[$age] = round(20 + ($age - 18) * 8);
            } elseif ($age <= 65) {
                $experienceYears = $age - 22;
                // Logarithmic mastery curve boosted by workshops and degrees
                $mastery = min(100, 52 + (log($experienceYears + 1) * 14 * $velocityMultiplier));
                $curve[$age] = round($mastery);
            } else {
                // Post 65: Advisory / Mentorship phase
                $curve[$age] = max(0, round(85 - (($age - 65) * 1.5)));
            }
        }

        return [
            'velocity_multiplier' => round($velocityMultiplier, 2),
            'has_degree' => $hasDegree,
            'workshop_count' => $skillWorkshopCount,
            'peak_mastery_age' => 52,
            'curve' => $curve,
        ];
    }
}
