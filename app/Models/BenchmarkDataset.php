<?php

namespace App\Models;

use App\Enums\BenchmarkSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenchmarkDataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'country_code',
        'metric_key',
        'age_bracket',
        'year',
        'value',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'source' => BenchmarkSource::class,
            'year' => 'integer',
            'value' => 'decimal:2',
        ];
    }
}
