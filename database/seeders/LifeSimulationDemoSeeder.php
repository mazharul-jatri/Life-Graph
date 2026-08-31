<?php

namespace Database\Seeders;

use App\Enums\EventCategory;
use App\Models\LifeActivity;
use App\Models\LifeEvent;
use App\Models\LifeTimeline;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LifeSimulationDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or retrieve demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@lifecurv.com'],
            [
                'name' => 'Mazharul Islam',
                'password' => Hash::make('password'),
            ]
        );

        // 2. User Profile (Age 25.0)
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'birth_date' => '2001-01-15',
                'current_age' => 25.0,
                'gender' => 'male',
                'country_code' => 'USA',
                'baseline_life_expectancy' => 78.5,
                'target_retirement_age' => 60,
                'current_savings' => 18500.00,
                'monthly_income' => 5000.00,
                'monthly_investment' => 650.00,
                'smoke_status' => 'never',
                'exercise_frequency_weekly' => 5,
            ]
        );

        // 3. Clear existing demo timelines & activities to ensure clean slate
        $user->timelines()->delete();
        $user->activities()->delete();

        // 4. Primary Timeline (Actual Grounded Life Path)
        $primaryTimeline = LifeTimeline::create([
            'user_id' => $user->id,
            'name' => 'Current Trajectory (Fitness & Investing)',
            'description' => 'Active daily gym habit, continuous learning, and disciplined index investing.',
            'is_primary' => true,
        ]);

        // 5. Activities logged by user on Primary Timeline
        // Activity 1: Daily Workout for 6 months
        LifeActivity::create([
            'user_id' => $user->id,
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Daily Workout & Strength Training (6 Months)',
            'activity_type' => 'workout',
            'pillar' => 'health',
            'frequency' => 'daily',
            'intensity_or_amount' => 60,
            'start_age' => 24.5,
            'end_age' => 25.0,
            'duration_months' => 6,
            'is_active' => true,
        ]);

        // Activity 2: Communication Skills & Leadership Workshop
        LifeActivity::create([
            'user_id' => $user->id,
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Executive Communication & Leadership Workshop',
            'activity_type' => 'workshop',
            'pillar' => 'career',
            'frequency' => 'one_time',
            'intensity_or_amount' => 1,
            'start_age' => 24.0,
            'duration_months' => 2,
            'is_active' => false,
        ]);

        // Activity 3: Bachelor Degree
        LifeActivity::create([
            'user_id' => $user->id,
            'timeline_id' => $primaryTimeline->id,
            'title' => 'B.Sc. in Computer Science & Engineering',
            'activity_type' => 'education',
            'pillar' => 'career',
            'frequency' => 'daily',
            'intensity_or_amount' => 4,
            'start_age' => 19.0,
            'end_age' => 23.0,
            'duration_months' => 48,
            'is_active' => false,
        ]);

        // Activity 4: Monthly Index Investing
        LifeActivity::create([
            'user_id' => $user->id,
            'timeline_id' => $primaryTimeline->id,
            'title' => 'S&P 500 Index Fund Compound Portfolio',
            'activity_type' => 'investment',
            'pillar' => 'wealth',
            'frequency' => 'monthly',
            'intensity_or_amount' => 650.00,
            'start_age' => 23.5,
            'duration_months' => 18,
            'is_active' => true,
        ]);

        // 6. Milestones for Primary Timeline
        // Historical Past Milestones (Age <= 25)
        LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Graduated B.Sc. with Distinction',
            'description' => 'Completed 4-year undergraduate program in Computer Science.',
            'category' => EventCategory::EDUCATION,
            'pillar' => 'career',
            'event_date' => '2024-01-15',
            'age_at_event' => 23.0,
            'is_projected' => false,
            'impact_score' => 4,
            'sort_order' => 1,
        ]);

        LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Promoted to Senior Software Engineer',
            'description' => 'Fast-track promotion accelerated by technical excellence & communication skills.',
            'category' => EventCategory::CAREER,
            'pillar' => 'career',
            'event_date' => '2025-06-01',
            'age_at_event' => 24.5,
            'is_projected' => false,
            'impact_score' => 5,
            'sort_order' => 2,
        ]);

        LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => '6-Month Daily Fitness Streak Achieved',
            'description' => 'Significant VO2 max improvement and biological age reduction.',
            'category' => EventCategory::HEALTH,
            'pillar' => 'health',
            'event_date' => '2026-01-10',
            'age_at_event' => 25.0,
            'is_projected' => false,
            'impact_score' => 4,
            'sort_order' => 3,
        ]);

        // Simulated Future Milestones (Age > 25)
        LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Principal Architect / Engineering Director',
            'description' => 'Projected leadership advancement based on continuous workshop skill compounding.',
            'category' => EventCategory::CAREER,
            'pillar' => 'career',
            'event_date' => '2031-01-01',
            'age_at_event' => 30.0,
            'is_projected' => true,
            'impact_score' => 5,
            'sort_order' => 4,
        ]);

        LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Financial Independence Crossover Point',
            'description' => 'Investment portfolio returns surpass annual cost of living (4% rule achieved).',
            'category' => EventCategory::FINANCE,
            'pillar' => 'wealth',
            'event_date' => '2053-06-01',
            'age_at_event' => 52.5,
            'is_projected' => true,
            'impact_score' => 5,
            'sort_order' => 5,
        ]);

        // 7. Counterfactual "What-If" Timeline (Scenario: Stop Gym, Start Smoking, Stop Investing)
        $whatIfTimeline = LifeTimeline::create([
            'user_id' => $user->id,
            'name' => 'What-If: Heavy Smoking & Sedentary Habit',
            'description' => 'Simulated counterfactual divergence: Stopped working out, started smoking 1 pack/day at Age 25.',
            'is_primary' => false,
            'parent_timeline_id' => $primaryTimeline->id,
        ]);

        // What-if specific negative activities
        LifeActivity::create([
            'user_id' => $user->id,
            'timeline_id' => $whatIfTimeline->id,
            'title' => 'Smoking 20 Cigarettes / Day',
            'activity_type' => 'smoking',
            'pillar' => 'health',
            'frequency' => 'daily',
            'intensity_or_amount' => 20,
            'start_age' => 25.0,
            'duration_months' => 240, // 20 years
            'is_active' => true,
        ]);

        LifeEvent::create([
            'timeline_id' => $whatIfTimeline->id,
            'title' => 'Decision Divergence Point',
            'description' => 'Forks from Age 25 baseline: Stopped fitness routine and adopted daily smoking.',
            'category' => EventCategory::HEALTH,
            'pillar' => 'health',
            'age_at_event' => 25.0,
            'is_projected' => true,
            'impact_score' => -2,
            'sort_order' => 1,
        ]);

        LifeEvent::create([
            'timeline_id' => $whatIfTimeline->id,
            'title' => 'Chronic Hypertension & Respiratory Decline',
            'description' => 'Direct biological penalty from 15+ pack-years of smoking and sedentary lifestyle.',
            'category' => EventCategory::HEALTH,
            'pillar' => 'health',
            'age_at_event' => 46.0,
            'is_projected' => true,
            'impact_score' => -4,
            'risk_alert' => 'Elevated Cardiovascular Morbidity',
            'sort_order' => 2,
        ]);
    }
}
