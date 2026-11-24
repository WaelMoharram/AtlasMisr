<?php

namespace Tests\Feature;

use App\Models\Era;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BilingualSiteTest extends TestCase
{
    use RefreshDatabase;

    private function seedContent(): Event
    {
        $era = Era::create([
            'name_ar' => 'الدولة القديمة', 'name_en' => 'Old Kingdom', 'start_year' => -2686, 'end_year' => -2181,
            'description' => 'وصف عربي', 'description_en' => 'English description', 'status' => 'published',
        ]);

        return Event::create([
            'era_id' => $era->id, 'title_ar' => 'بناء الهرم', 'title_en' => 'Building the pyramid',
            'event_year' => -2560, 'date_precision' => 'approximate', 'latitude' => 29.97, 'longitude' => 31.13,
            'description' => 'نص عربي', 'description_en' => 'English text', 'status' => 'published',
        ]);
    }

    public function test_landing_and_map_render_in_both_languages(): void
    {
        $this->seedContent();

        $this->get('/')->assertOk()->assertSee('dir="rtl"', false)->assertSee('الدولة القديمة');
        $this->get('/en')->assertOk()->assertSee('dir="ltr"', false)->assertSee('Old Kingdom')->assertSee('English description');
        $this->get('/map')->assertOk()->assertSee('locale: "ar"', false);
        $this->get('/en/map')->assertOk()->assertSee('locale: "en"', false)->assertSee('hreflang="ar"', false);
    }

    public function test_api_exposes_english_fields(): void
    {
        $this->seedContent();

        $this->getJson('/api/events')->assertOk()
            ->assertJsonPath('data.0.title_en', 'Building the pyramid')
            ->assertJsonPath('data.0.description_en', 'English text');
        $this->getJson('/api/eras')->assertOk()->assertJsonPath('data.0.description_en', 'English description');
    }
}
