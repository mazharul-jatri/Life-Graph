<?php

namespace App\Services\Simulation;

use App\Models\LifeTimeline;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Collection;

class LifeProjectionEngine
{
    public function __construct(
        protected HealthActuarialModel $healthModel,
        protected WealthCompoundingModel $wealthModel,
        protected CareerVelocityModel $careerModel
    ) {}

    /**
     * Generate full 0-120 multi-pillar simulation payload for a user and their timelines.
     *
     * @param User $user
     * @return array
     */
    public function simulate(User $user): array
    {
        $profile = $user->profile ?? new UserProfile([
            'current_age' => 25.0,
            'baseline_life_expectancy' => 78.5,
            'target_retirement_age' => 60,
            'current_savings' => 15000.0,
            'monthly_income' => 4500.0,
            'monthly_investment' => 500.0,
            'gender' => 'male',
            'country_code' => 'USA',
        ]);

        $currentAge = (float) ($profile->current_age ?? 25.0);
        $timelines = $user->timelines()->with(['events', 'activities'])->get();

        if ($timelines->isEmpty()) {
            // Return default baseline structure
            $timelines = collect([
                new LifeTimeline([
                    'id' => 1,
                    'name' => 'Primary Trajectory',
                    'is_primary' => true,
                ])
            ]);
        }

        $allUserActivities = $user->activities()->get();

        $processedTimelines = [];
        $baselineMetrics = null;

        foreach ($timelines as $timeline) {
            // Merge global user activities with timeline-specific activities
            $timelineActivities = $allUserActivities
                ->where(fn($act) => is_null($act->timeline_id) || $act->timeline_id === $timeline->id);

            // Execute simulations
            $healthResult = $this->healthModel->calculate(
                $currentAge,
                (float) ($profile->baseline_life_expectancy ?? 78.5),
                $timelineActivities
            );

            $wealthResult = $this->wealthModel->calculate(
                $currentAge,
                (float) ($profile->current_savings ?? 15000.0),
                (float) ($profile->monthly_income ?? 4500.0),
                (float) ($profile->monthly_investment ?? 500.0),
                (int) ($profile->target_retirement_age ?? 60),
                $timelineActivities
            );

            $careerResult = $this->careerModel->calculate(
                $currentAge,
                $timelineActivities
            );

            // Synthesize multi-pillar vector array from age 0 to 120
            $curvePoints = [];
            for ($age = 0; $age <= 120; $age++) {
                $hScore = $healthResult['curve'][$age] ?? 0;
                $wVal = $wealthResult['curve'][$age] ?? 0;
                $cScore = $careerResult['curve'][$age] ?? 0;

                // Normalized wealth score (0 - 100) for overall index display
                $wScoreNorm = min(100, round(($wVal / max(1, $wealthResult['fi_threshold'] ?? 1000000)) * 100));

                // Overall Life Quality Index (0 - 100)
                $overallScore = round(($hScore * 0.45) + ($wScoreNorm * 0.30) + ($cScore * 0.25), 1);

                $curvePoints[] = [
                    'age' => $age,
                    'health' => $hScore,
                    'wealth' => $wVal,
                    'wealth_norm' => $wScoreNorm,
                    'career' => $cScore,
                    'overall' => $overallScore,
                ];
            }

            // Format milestones
            $events = $timeline->events ?? collect();
            $formattedMilestones = $events->map(function ($event) use ($currentAge) {
                $age = $event->age_at_event ?? round($currentAge, 1);
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'category' => $event->category instanceof \App\Enums\EventCategory ? $event->category->value : (string)$event->category,
                    'pillar' => $event->pillar ?? 'career',
                    'age' => (float) $age,
                    'is_projected' => (bool) $event->is_projected,
                    'impact' => (int) $event->impact_score,
                    'risk_alert' => $event->risk_alert,
                ];
            })->values()->all();

            // Inject risk alert milestone if health model flags critical risk
            if (!empty($healthResult['cardiovascular_risk_age']) && !$timeline->is_primary) {
                $riskAge = $healthResult['cardiovascular_risk_age'];
                $formattedMilestones[] = [
                    'id' => 9999,
                    'title' => 'Critical Cardiovascular Risk Inflection',
                    'description' => 'Epidemiological threshold where smoking compounding sharply elevates cardiac risk.',
                    'category' => 'Health',
                    'pillar' => 'health',
                    'age' => (float) $riskAge,
                    'is_projected' => true,
                    'impact' => -5,
                    'risk_alert' => 'Critical Health Decline Risk',
                ];
            }

            $timelineSummary = [
                'id' => $timeline->id,
                'name' => $timeline->name,
                'is_primary' => (bool) $timeline->is_primary,
                'parent_id' => $timeline->parent_timeline_id,
                'branch_at_age' => $timeline->is_primary ? null : $currentAge,
                'color' => $timeline->is_primary ? '#06b6d4' : '#c084fc',
                'metrics' => [
                    'biological_age' => $healthResult['biological_age'],
                    'projected_lifespan' => $healthResult['projected_lifespan'],
                    'projected_retirement_nw' => $wealthResult['projected_retirement_net_worth'],
                    'financial_freedom_age' => $wealthResult['projected_financial_freedom_age'],
                    'career_velocity' => $careerResult['velocity_multiplier'],
                ],
                'curve_data' => $curvePoints,
                'milestones' => $formattedMilestones,
                'activities' => $timelineActivities->values()->all(),
            ];

            if ($timeline->is_primary) {
                $baselineMetrics = $timelineSummary['metrics'];
            }

            $processedTimelines[] = $timelineSummary;
        }

        return [
            'user_profile' => [
                'name' => $user->name,
                'current_age' => $currentAge,
                'max_display_age' => 120,
                'currency' => $profile->currency ?? 'USD',
                'baseline_life_expectancy' => (float) ($profile->baseline_life_expectancy ?? 78.5),
                'target_retirement_age' => (int) ($profile->target_retirement_age ?? 60),
                'current_savings' => (float) ($profile->current_savings ?? 15000.0),
                'monthly_investment' => (float) ($profile->monthly_investment ?? 500.0),
            ],
            'baseline_metrics' => $baselineMetrics ?? $processedTimelines[0]['metrics'] ?? [],
            'timelines' => $processedTimelines,
        ];
    }
}
