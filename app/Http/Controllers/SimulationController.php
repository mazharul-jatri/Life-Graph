<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Simulation\LifeProjectionEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function __construct(
        protected LifeProjectionEngine $engine
    ) {}

    /**
     * Compute and return 0-120 simulation data.
     */
    public function compute(Request $request): JsonResponse
    {
        $user = $request->user() ?? User::with(['profile', 'timelines.events', 'activities'])->first();
        if (!$user) {
            return response()->json(['error' => 'No user found'], 404);
        }

        $user->loadMissing(['profile', 'timelines.events', 'activities']);
        $data = $this->engine->simulate($user);

        return response()->json($data);
    }

    /**
     * Update user profile baseline parameters (current age, monthly investment, currency, etc.)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_age' => 'nullable|numeric|min:1|max:120',
            'current_savings' => 'nullable|numeric|min:0',
            'monthly_income' => 'nullable|numeric|min:0',
            'monthly_investment' => 'nullable|numeric|min:0',
            'target_retirement_age' => 'nullable|integer|min:30|max:100',
            'currency' => 'nullable|string|in:USD,BDT,EUR,GBP,INR,CAD,AUD,JPY',
        ]);

        $user = $request->user() ?? User::first();
        if ($user && $user->profile) {
            $user->profile->update(array_filter($validated, fn($v) => !is_null($v)));
        }

        $user->loadMissing(['profile', 'timelines.events', 'activities']);
        $data = $this->engine->simulate($user);

        return response()->json([
            'message' => 'Profile updated and re-simulated.',
            'simulation' => $data,
        ]);
    }
}
