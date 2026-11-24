<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EraResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
            'description' => $this->description,
            'description_en' => $this->description_en,
            'boundary_geojson' => $this->boundary_geojson,
            'source' => new SourceResource($this->whenLoaded('source')),
        ];
    }
}
