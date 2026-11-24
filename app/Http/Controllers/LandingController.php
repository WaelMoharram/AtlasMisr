<?php

namespace App\Http\Controllers;

use App\Models\Era;
use App\Models\Event;
use App\Models\Source;

class LandingController extends Controller
{
    public function __invoke()
    {
        $eras = Era::query()->published()->withCount(['events' => fn ($q) => $q->published()])->orderBy('start_year')->get();

        // Events that have an illustration in public/images/events/{id}.webp are shown as story cards.
        $stories = Event::query()->published()->orderBy('id')->get()
            ->filter(fn (Event $e) => file_exists(public_path("images/events/{$e->id}.webp")))
            ->take(4)->values();

        return view('landing', [
            'eras' => $eras,
            'stories' => $stories,
            'stats' => [
                'eras' => $eras->count(),
                'events' => Event::query()->published()->count(),
                'sources' => Source::query()->count(),
            ],
            'hasHero' => file_exists(public_path('images/hero.webp')),
            'hasMapShot' => file_exists(public_path('images/map-preview.webp')),
        ]);
    }
}
