<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'era_id',
        'title_ar',
        'title_en',
        'event_year',
        'event_month',
        'event_day',
        'date_precision',
        'latitude',
        'longitude',
        'location_name',
        'category',
        'description',
        'media',
        'source_id',
        'status',
    ];

    protected $casts = [
        'event_year' => 'integer',
        'event_month' => 'integer',
        'event_day' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'media' => 'array',
    ];

    public function era(): BelongsTo
    {
        return $this->belongsTo(Era::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeWithinBbox(Builder $query, float $minLng, float $minLat, float $maxLng, float $maxLat): Builder
    {
        return $query
            ->whereBetween('longitude', [$minLng, $maxLng])
            ->whereBetween('latitude', [$minLat, $maxLat]);
    }
}
