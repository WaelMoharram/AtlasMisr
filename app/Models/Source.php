<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = [
        'title',
        'author',
        'reference',
        'reliability_note',
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
