<?php

namespace App\Http\Controllers;

use App\Enums\EventCategory;
use App\Models\LifeEvent;
use App\Models\LifeTimeline;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/simulator')->with('success', 'Welcome back to Lifecurv!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'current_age' => 'required|numeric|min:10|max:100',
            'current_savings' => 'nullable|numeric|min:0',
            'currency' => 'required|string|in:USD,BDT,EUR,GBP,INR,CAD,AUD,JPY',
            'country_code' => 'nullable|string|max:3',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $currentAge = (float) $validated['current_age'];
        $currency = $validated['currency'];

        // Default savings/income baseline tailored by currency
        $defaultSavings = $currency === 'BDT' ? 200000.0 : ($currency === 'INR' ? 150000.0 : 15000.0);
        $userSavings = !empty($validated['current_savings']) ? (float) $validated['current_savings'] : $defaultSavings;
        $defaultIncome = $currency === 'BDT' ? 65000.0 : ($currency === 'INR' ? 50000.0 : 4500.0);
        $defaultInvestment = $currency === 'BDT' ? 15000.0 : ($currency === 'INR' ? 10000.0 : 500.0);

        UserProfile::create([
            'user_id' => $user->id,
            'current_age' => $currentAge,
            'currency' => $currency,
            'country_code' => $validated['country_code'] ?? ($currency === 'BDT' ? 'BGD' : 'USA'),
            'baseline_life_expectancy' => 78.5,
            'target_retirement_age' => 60,
            'current_savings' => $userSavings,
            'monthly_income' => $defaultIncome,
            'monthly_investment' => $defaultInvestment,
            'smoke_status' => 'never',
            'exercise_frequency_weekly' => 3,
        ]);

        // Initialize Primary Timeline
        $timeline = LifeTimeline::create([
            'user_id' => $user->id,
            'name' => 'Primary Life Trajectory',
            'description' => 'Baseline ground-truth timeline and projected future.',
            'is_primary' => true,
        ]);

        // Add starting milestone
        LifeEvent::create([
            'timeline_id' => $timeline->id,
            'title' => 'Lifecurv Trajectory Initialized',
            'description' => "Initial baseline established at Age {$currentAge}.",
            'category' => EventCategory::PERSONAL,
            'pillar' => 'personal',
            'event_date' => now()->format('Y-m-d'),
            'age_at_event' => $currentAge,
            'is_projected' => false,
            'impact_score' => 3,
            'sort_order' => 1,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/simulator')->with('success', 'Account created! Your 0–120 lifespan trajectory has been initialized.');
    }

    public function loginAsDemo(Request $request): RedirectResponse
    {
        $demoUser = User::where('email', 'demo@lifecurv.com')->first();

        if (!$demoUser) {
            // If seeder hasn't run, create a demo user on the fly
            $demoUser = User::create([
                'name' => 'Mazharul Islam (Demo)',
                'email' => 'demo@lifecurv.com',
                'password' => Hash::make('password'),
            ]);

            UserProfile::create([
                'user_id' => $demoUser->id,
                'current_age' => 25.0,
                'currency' => 'USD',
                'country_code' => 'USA',
                'baseline_life_expectancy' => 78.5,
                'target_retirement_age' => 60,
                'current_savings' => 18500.0,
                'monthly_income' => 5000.0,
                'monthly_investment' => 650.0,
            ]);
        }

        Auth::login($demoUser);
        $request->session()->regenerate();

        return redirect('/simulator')->with('success', 'Logged in as Demo User with pre-calibrated 25-year-old trajectory!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out.');
    }
}
