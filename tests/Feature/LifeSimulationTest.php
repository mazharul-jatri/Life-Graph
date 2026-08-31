<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifeSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulation_api_returns_0_to_120_points(): void
    {
        $this->seed();

        $response = $this->getJson('/api/simulation');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'user_profile' => [
                'name',
                'current_age',
                'max_display_age',
                'baseline_life_expectancy',
            ],
            'baseline_metrics',
            'timelines' => [
                '*' => [
                    'id',
                    'name',
                    'is_primary',
                    'metrics',
                    'curve_data',
                    'milestones',
                    'activities',
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertCount(121, $data['timelines'][0]['curve_data']);
    }

    public function test_can_log_new_activity(): void
    {
        $this->seed();
        $user = User::first();

        $response = $this->post('/activities', [
            'title' => 'Daily Strength Training',
            'activity_type' => 'workout',
            'pillar' => 'health',
            'frequency' => 'daily',
            'intensity_or_amount' => 45,
            'start_age' => 25.0,
            'duration_months' => 12,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('life_activities', [
            'title' => 'Daily Strength Training',
            'activity_type' => 'workout',
        ]);
    }
}
