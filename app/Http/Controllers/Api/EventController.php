<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'era_id' => ['nullable', 'integer', 'exists:eras,id'],
            'bbox' => ['nullable', 'string', 'regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?,-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'],
        ]);

        $events = Event::query()
            ->published()
            ->with(['source', 'frames'])
            ->when($validated['era_id'] ?? null, fn ($query, $eraId) => $query->where('era_id', $eraId))
            ->when($validated['bbox'] ?? null, function ($query, $bbox) {
                [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', explode(',', $bbox));

                return $query->withinBbox($minLng, $minLat, $maxLng, $maxLat);
            })
            ->orderBy('event_year')
            ->get();

        return EventResource::collection($events);
    }
}
