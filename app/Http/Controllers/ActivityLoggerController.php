<?php

namespace App\Http\Controllers;

use App\Models\LifeActivity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivityLoggerController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'activity_type' => 'required|string',
            'pillar' => 'required|string|in:health,wealth,career,personal',
            'frequency' => 'required|string|in:daily,weekly,monthly,one_time',
            'intensity_or_amount' => 'nullable|numeric|min:0',
            'start_age' => 'required|numeric|min:0|max:120',
            'end_age' => 'nullable|numeric|min:0|max:120',
            'duration_months' => 'nullable|integer|min:1|max:1200',
            'timeline_id' => 'nullable|exists:life_timelines,id',
        ]);

        $user = $request->user() ?? User::first();

        $user->activities()->create([
            'timeline_id' => $validated['timeline_id'] ?? null,
            'title' => $validated['title'],
            'activity_type' => $validated['activity_type'],
            'pillar' => $validated['pillar'],
            'frequency' => $validated['frequency'],
            'intensity_or_amount' => $validated['intensity_or_amount'] ?? 0,
            'start_age' => $validated['start_age'],
            'end_age' => $validated['end_age'] ?? null,
            'duration_months' => $validated['duration_months'] ?? 6,
            'is_active' => true,
        ]);

        return back()->with('success', 'Life activity logged successfully.');
    }

    public function toggle(LifeActivity $activity): RedirectResponse
    {
        $activity->update([
            'is_active' => !$activity->is_active,
        ]);

        return back()->with('success', 'Activity status toggled.');
    }

    public function destroy(LifeActivity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('success', 'Activity removed.');
    }
}
