<?php

namespace App\Models;

use App\Support\Moment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFrame extends Model
{
    protected $fillable = [
        'event_id',
        'occurs_year',
        'occurs_month',
        'occurs_day',
        'occurs_hour',
        'occurs_minute',
        'geometry_type',
        'geometry',
        'icon',
        'label',
        'side',
        'troop_count',
        'casualties',
        'sort_order',
    ];

    protected $casts = [
        'occurs_year' => 'integer',
        'occurs_month' => 'integer',
        'occurs_day' => 'integer',
        'occurs_hour' => 'integer',
        'occurs_minute' => 'integer',
        'geometry' => 'array',
        'troop_count' => 'integer',
        'casualties' => 'integer',
        'sort_order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * A sortable integer representing this frame's point in time, so
     * frames (and events) can be compared/ordered without a real DATETIME
     * column, which cannot represent BCE years.
     */
    public function moment(): int
    {
        return Moment::pack(
            $this->occurs_year,
            $this->occurs_month,
            $this->occurs_day,
            $this->occurs_hour,
            $this->occurs_minute,
        );
    }
}
