<?php

namespace App\Services;

use App\Models\LifeTimeline;
use Illuminate\Support\Collection;

class TimelineTreeBuilder
{
    /**
     * Build the timeline tree JSON contract from a collection of timelines.
     *
     * @param  Collection<int, LifeTimeline>|array<int, LifeTimeline>  $timelines
     * @return array{timelines: array<int, array<string, mixed>>}
     */
    public function build(iterable $timelines): array
    {
        $timelinesCollection = $timelines instanceof Collection ? $timelines : collect($timelines);

        // Ensure relations are loaded if not already
        $timelinesCollection->loadMissing(['events', 'branchPointEvent']);

        // Separate primary and branch timelines to guarantee lane 0 for primary
        $primary = $timelinesCollection->firstWhere('is_primary', true)
            ?? $timelinesCollection->firstWhere('parent_timeline_id', null)
            ?? $timelinesCollection->first();

        $orderedTimelines = collect();
        if ($primary) {
            $orderedTimelines->push($primary);
        }

        $branches = $timelinesCollection->filter(function ($timeline) use ($primary) {
            return $primary ? $timeline->id !== $primary->id : true;
        })->sortBy('id');

        $orderedTimelines = $orderedTimelines->concat($branches);

        $laneIndex = 0;
        $result = [];

        foreach ($orderedTimelines as $timeline) {
            $branchAtX = null;
            if (!$timeline->is_primary && $timeline->branchPointEvent && $timeline->branchPointEvent->event_date) {
                $branchAtX = $timeline->branchPointEvent->event_date->format('Y-m');
            }

            $events = [];
            foreach ($timeline->events as $event) {
                $category = $event->category;
                if ($category instanceof \BackedEnum) {
                    $category = $category->value;
                }

                $eventDate = $event->event_date ? $event->event_date->format('Y-m') : null;

                $eventData = [
                    'x' => $eventDate,
                    'y' => $laneIndex,
                    'title' => $event->title,
                    'category' => (string) $category,
                    'impact' => (int) $event->impact_score,
                    'is_projected' => (bool) $event->is_projected,
                ];

                if ($event->description) {
                    $eventData['description'] = $event->description;
                }

                $events[] = $eventData;
            }

            $result[] = [
                'id' => (int) $timeline->id,
                'name' => (string) $timeline->name,
                'is_primary' => (bool) $timeline->is_primary,
                'parent_id' => $timeline->parent_timeline_id ? (int) $timeline->parent_timeline_id : null,
                'branch_at_x' => $branchAtX,
                'events' => $events,
            ];

            $laneIndex++;
        }

        return [
            'timelines' => $result,
        ];
    }
}
