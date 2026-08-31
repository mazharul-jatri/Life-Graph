<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->date('birth_date')->nullable();
            $table->decimal('current_age', 4, 1)->default(25.0);
            $table->string('gender')->default('male');
            $table->string('country_code', 3)->default('USA');
            $table->decimal('baseline_life_expectancy', 4, 1)->default(78.5);
            $table->integer('target_retirement_age')->default(60);
            $table->decimal('current_savings', 14, 2)->default(15000.00);
            $table->decimal('monthly_income', 12, 2)->default(4500.00);
            $table->decimal('monthly_investment', 12, 2)->default(500.00);
            $table->string('smoke_status')->default('never');
            $table->integer('exercise_frequency_weekly')->default(4);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('life_events', function (Blueprint $table) {
            $table->decimal('age_at_event', 4, 1)->nullable()->after('event_date');
            $table->string('pillar')->nullable()->after('category');
            $table->string('risk_alert')->nullable()->after('impact_score');
        });

        Schema::create('life_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('timeline_id')->nullable()->constrained('life_timelines')->cascadeOnDelete();
            $table->string('title');
            $table->string('activity_type'); // e.g. workout, workshop, degree, investment, smoking
            $table->string('pillar')->default('health'); // health, wealth, career, personal
            $table->string('frequency')->default('daily'); // daily, weekly, monthly, one_time
            $table->decimal('intensity_or_amount', 12, 2)->default(0);
            $table->decimal('start_age', 4, 1)->default(20.0);
            $table->decimal('end_age', 4, 1)->nullable();
            $table->integer('duration_months')->default(6);
            $table->json('impact_coefficients')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('habit_impact_rules', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type')->unique();
            $table->string('display_name');
            $table->string('pillar'); // health, wealth, career, personal
            $table->string('unit')->default('session');
            $table->decimal('delta_life_expectancy', 5, 2)->default(0.00);
            $table->decimal('delta_cardiac_risk_pct', 5, 2)->default(0.00);
            $table->decimal('delta_career_multiplier', 4, 2)->default(1.00);
            $table->decimal('wealth_annual_roi_pct', 5, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_impact_rules');
        Schema::dropIfExists('life_activities');
        
        Schema::table('life_events', function (Blueprint $table) {
            $table->dropColumn(['age_at_event', 'pillar', 'risk_alert']);
        });

        Schema::dropIfExists('user_profiles');
    }
};
