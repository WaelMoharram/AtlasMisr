<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Era extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'start_year',
        'end_year',
        'description',
        'boundary_geojson',
        'source_id',
        'status',
    ];

    protected $casts = [
        'boundary_geojson' => 'array',
        'start_year' => 'integer',
        'end_year' => 'integer',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
