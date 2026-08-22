<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'era_id' => $this->era_id,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'event_year' => $this->event_year,
            'event_month' => $this->event_month,
            'event_day' => $this->event_day,
            'event_hour' => $this->event_hour,
            'event_minute' => $this->event_minute,
            'date_precision' => $this->date_precision,
            'start_moment' => $this->startMoment(),
            'end_moment' => $this->endMoment(),
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'location_name' => $this->location_name,
            'category' => $this->category,
            'description' => $this->description,
            'media' => collect($this->media)->map(fn (string $path) => Storage::disk('public')->url($path))->all(),
            'video_url' => $this->video_url,
            'source' => new SourceResource($this->whenLoaded('source')),
            'frames' => EventFrameResource::collection($this->whenLoaded('frames')),
        ];
    }
}
