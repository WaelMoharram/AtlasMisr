<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = [
        'title',
        'title_en',
        'author',
        'reference',
        'reliability_note',
        'reliability_note_en',
    ];

    public function eras(): HasMany
    {
        return $this->hasMany(Era::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
