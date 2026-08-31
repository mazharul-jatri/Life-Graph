<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('life_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timeline_id')->constrained('life_timelines')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->date('event_date')->nullable();
            $table->boolean('is_projected')->default(false);
            $table->tinyInteger('impact_score')->default(0);
            $table->json('metadata')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('life_timelines', function (Blueprint $table) {
            $table->foreign('branch_point_event_id')
                ->references('id')
                ->on('life_events')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('life_timelines', function (Blueprint $table) {
            $table->dropForeign(['branch_point_event_id']);
        });

        Schema::dropIfExists('life_events');
    }
};
