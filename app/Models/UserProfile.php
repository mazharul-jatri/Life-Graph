<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'birth_date',
        'current_age',
        'gender',
        'country_code',
        'currency',
        'baseline_life_expectancy',
        'target_retirement_age',
        'current_savings',
        'monthly_income',
        'monthly_investment',
        'smoke_status',
        'exercise_frequency_weekly',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'current_age' => 'float',
            'baseline_life_expectancy' => 'float',
            'target_retirement_age' => 'integer',
            'current_savings' => 'float',
            'monthly_income' => 'float',
            'monthly_investment' => 'float',
            'exercise_frequency_weekly' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
