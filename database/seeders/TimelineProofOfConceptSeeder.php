<?php

namespace Database\Seeders;

use App\Enums\EventCategory;
use App\Models\DecisionPoint;
use App\Models\LifeEvent;
use App\Models\LifeTimeline;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TimelineProofOfConceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@lifecurv.app'],
            [
                'name' => 'Mazharul Islam',
                'password' => Hash::make('password'),
            ]
        );

        // 1. Primary Timeline: Actual Career & Life Path
        $primaryTimeline = LifeTimeline::create([
            'user_id' => $user->id,
            'name' => 'Actual Life Path',
            'description' => 'My real journey through tech and software engineering.',
            'is_primary' => true,
            'parent_timeline_id' => null,
            'branch_point_event_id' => null,
        ]);

        $event1 = LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Started BSc in Computer Science',
            'description' => 'Enrolled at university majoring in Software Engineering.',
            'category' => EventCategory::EDUCATION,
            'event_date' => '2019-01-15',
            'is_projected' => false,
            'impact_score' => 2,
            'sort_order' => 1,
        ]);

        $event2 = LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'First Software Engineering Internship',
            'description' => 'Full-stack development intern working on web applications.',
            'category' => EventCategory::CAREER,
            'event_date' => '2021-06-01',
            'is_projected' => false,
            'impact_score' => 3,
            'sort_order' => 2,
        ]);

        $event3 = LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Graduated with Honours',
            'description' => 'Completed undergraduate thesis in distributed systems.',
            'category' => EventCategory::EDUCATION,
            'event_date' => '2023-01-20',
            'is_projected' => false,
            'impact_score' => 4,
            'sort_order' => 3,
        ]);

        // Middle fork event
        $forkEvent = LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Joined Jatri as Senior Software Engineer',
            'description' => 'Decided to enter high-growth tech industry rather than academia.',
            'category' => EventCategory::CAREER,
            'event_date' => '2023-06-15',
            'is_projected' => false,
            'impact_score' => 5,
            'sort_order' => 4,
        ]);

        $event5 = LifeEvent::create([
            'timeline_id' => $primaryTimeline->id,
            'title' => 'Tech Lead & System Architect',
            'description' => 'Leading core platform architecture and infrastructure.',
            'category' => EventCategory::CAREER,
            'event_date' => '2025-01-10',
            'is_projected' => false,
            'impact_score' => 4,
            'sort_order' => 5,
        ]);

        // Decision point attached to fork event
        DecisionPoint::create([
            'event_id' => $forkEvent->id,
            'prompt' => 'What if I accepted the university research fellowship and stayed in academia?',
        ]);

        // 2. Branch Timeline: Hypothetical Academic Path
        $branchTimeline = LifeTimeline::create([
            'user_id' => $user->id,
            'name' => 'Hypothetical: Stayed in Academia',
            'description' => 'Alternative trajectory pursuing MSc & PhD in Machine Learning.',
            'is_primary' => false,
            'parent_timeline_id' => $primaryTimeline->id,
            'branch_point_event_id' => $forkEvent->id,
        ]);

        LifeEvent::create([
            'timeline_id' => $branchTimeline->id,
            'title' => 'Started MSc by Research in AI',
            'description' => 'Full-time graduate research fellowship in neural architectures.',
            'category' => EventCategory::EDUCATION,
            'event_date' => '2023-09-01',
            'is_projected' => true,
            'impact_score' => 3,
            'sort_order' => 1,
        ]);

        LifeEvent::create([
            'timeline_id' => $branchTimeline->id,
            'title' => 'Published First NeurIPS Paper',
            'description' => 'Primary author on novel deep learning optimization paper.',
            'category' => EventCategory::EDUCATION,
            'event_date' => '2025-06-15',
            'is_projected' => true,
            'impact_score' => 4,
            'sort_order' => 2,
        ]);

        LifeEvent::create([
            'timeline_id' => $branchTimeline->id,
            'title' => 'Commenced Doctoral PhD Fellowship in Europe',
            'description' => 'Funded doctoral position researching foundation models.',
            'category' => EventCategory::CAREER,
            'event_date' => '2026-09-01',
            'is_projected' => true,
            'impact_score' => 5,
            'sort_order' => 3,
        ]);
    }
}
