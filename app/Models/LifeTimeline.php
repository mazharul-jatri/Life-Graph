<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifeTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_primary',
        'parent_timeline_id',
        'branch_point_event_id',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(LifeEvent::class, 'timeline_id')->orderBy('event_date')->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LifeTimeline::class, 'parent_timeline_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(LifeTimeline::class, 'parent_timeline_id');
    }

    public function branchPointEvent(): BelongsTo
    {
        return $this->belongsTo(LifeEvent::class, 'branch_point_event_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LifeActivity::class, 'timeline_id');
    }
}
