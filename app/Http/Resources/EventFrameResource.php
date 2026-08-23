<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventFrameResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'moment' => $this->moment(),
            'occurs_year' => $this->occurs_year,
            'occurs_month' => $this->occurs_month,
            'occurs_day' => $this->occurs_day,
            'occurs_hour' => $this->occurs_hour,
            'occurs_minute' => $this->occurs_minute,
            'geometry_type' => $this->geometry_type,
            'geometry' => $this->geometry,
            'icon' => $this->icon,
            'label' => $this->label,
            'side' => $this->side,
            'troop_count' => $this->troop_count,
            'casualties' => $this->casualties,
        ];
    }
}
