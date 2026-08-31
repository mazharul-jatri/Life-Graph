<?php

use App\Http\Controllers\ActivityLoggerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TimelineController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', [TimelineController::class, 'landing'])->name('landing');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/demo-login', [AuthController::class, 'loginAsDemo'])->name('demo.login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Simulator & Timeline Dashboard (Public demo fallback or Authenticated)
Route::get('/simulator', [TimelineController::class, 'show'])->name('simulator');
Route::get('/timeline', [TimelineController::class, 'show'])->name('timeline.show');
Route::get('/dashboard', [TimelineController::class, 'show'])->name('dashboard');

// Activity Logger Routes
Route::post('/activities', [ActivityLoggerController::class, 'store'])->name('activities.store');
Route::patch('/activities/{activity}/toggle', [ActivityLoggerController::class, 'toggle'])->name('activities.toggle');
Route::delete('/activities/{activity}', [ActivityLoggerController::class, 'destroy'])->name('activities.destroy');

// Real-time Simulation Engine API
Route::get('/api/simulation', [SimulationController::class, 'compute'])->name('simulation.compute');
Route::post('/api/profile/update', [SimulationController::class, 'updateProfile'])->name('simulation.profile.update');
