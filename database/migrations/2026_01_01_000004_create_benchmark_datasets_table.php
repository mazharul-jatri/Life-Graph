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
        Schema::create('benchmark_datasets', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50);
            $table->char('country_code', 3);
            $table->string('metric_key', 100);
            $table->string('age_bracket', 50)->nullable();
            $table->integer('year');
            $table->decimal('value', 10, 2);
            $table->string('unit', 50);
            $table->timestamps();

            $table->unique(['source', 'country_code', 'metric_key', 'age_bracket', 'year'], 'benchmark_datasets_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmark_datasets');
    }
};
