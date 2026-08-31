<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Simulation\LifeProjectionEngine;
use App\Services\TimelineTreeBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TimelineController extends Controller
{
    public function __construct(
        protected LifeProjectionEngine $projectionEngine,
        protected TimelineTreeBuilder $treeBuilder
    ) {}

    /**
     * Display the public landing page (redirects to simulator if already authenticated).
     */
    public function landing(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('simulator');
        }

        // Provide sample preview simulation data for the landing page hero teaser
        $demoUser = User::with(['profile', 'timelines.events', 'timelines.activities', 'activities'])
            ->where('email', 'demo@lifecurv.com')
            ->first() ?? User::with(['profile', 'timelines.events', 'timelines.activities', 'activities'])->first();

        $previewSimulation = $demoUser ? $this->projectionEngine->simulate($demoUser) : [];

        return Inertia::render('Landing', [
            'previewSimulation' => $previewSimulation,
        ]);
    }

    /**
     * Display the 0-120 Lifespan Trajectory & Branching Timeline dashboard.
     */
    public function show(Request $request): Response
    {
        $user = $request->user() ?? User::with(['profile', 'timelines.events', 'timelines.activities', 'activities'])->first();

        if ($user) {
            $user->loadMissing(['profile', 'timelines.events', 'timelines.activities', 'activities']);
        }

        $simulationData = $user ? $this->projectionEngine->simulate($user) : [];
        $timelines = $user ? $user->timelines : collect();
        $branchingData = $this->treeBuilder->build($timelines);

        $activities = $user ? $user->activities()->orderBy('created_at', 'desc')->get() : collect();

        return Inertia::render('Timeline/Show', [
            'simulationData' => $simulationData,
            'timelineData' => $branchingData,
            'activities' => $activities,
            'user' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile,
            ] : null,
        ]);
    }
}
