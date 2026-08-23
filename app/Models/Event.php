<?php

namespace App\Models;

use App\Support\Moment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'era_id',
        'title_ar',
        'title_en',
        'event_year',
        'event_month',
        'event_day',
        'event_hour',
        'event_minute',
        'date_precision',
        'ends_year',
        'ends_month',
        'ends_day',
        'ends_hour',
        'ends_minute',
        'latitude',
        'longitude',
        'location_name',
        'category',
        'description',
        'media',
        'video_url',
        'side_a_label',
        'side_b_label',
        'source_id',
        'status',
    ];

    protected $casts = [
        'event_year' => 'integer',
        'event_month' => 'integer',
        'event_day' => 'integer',
        'event_hour' => 'integer',
        'event_minute' => 'integer',
        'ends_year' => 'integer',
        'ends_month' => 'integer',
        'ends_day' => 'integer',
        'ends_hour' => 'integer',
        'ends_minute' => 'integer',
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

    public function frames(): HasMany
    {
        return $this->hasMany(EventFrame::class)->orderBy('occurs_year')->orderBy('occurs_month')->orderBy('occurs_day')->orderBy('occurs_hour')->orderBy('occurs_minute')->orderBy('sort_order');
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

    public function startMoment(): int
    {
        return Moment::pack($this->event_year, $this->event_month, $this->event_day, $this->event_hour, $this->event_minute);
    }

    /**
     * When the event stops being "active" and disappears from the
     * timeline. Uses the explicit ends_* fields if set, otherwise defaults
     * to right after the event's own precision (e.g. a year-only event
     * stays active for that whole year).
     */
    public function endMoment(): int
    {
        if ($this->ends_year !== null) {
            return Moment::pack($this->ends_year, $this->ends_month, $this->ends_day, $this->ends_hour, $this->ends_minute);
        }

        return Moment::packEndOfPrecision($this->event_year, $this->event_month, $this->event_day, $this->event_hour, $this->event_minute);
    }
}
