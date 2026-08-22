<?php

namespace Database\Seeders;

use App\Models\Era;
use App\Models\Event;
use App\Models\EventFrame;
use App\Models\Source;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    /**
     * Seeds the two demo events for the AtlasMisr v1 demo: the October War
     * Suez Canal crossing (1973) and the liberation of Taba (1989).
     */
    public function run(): void
    {
        $crossingSource = Source::create([
            'title' => 'The Crossing of the Suez Canal, October 6 1973',
            'author' => 'Defense Technical Information Center (DTIC)',
            'reference' => 'https://apps.dtic.mil/sti/citations/ADA379531',
            'reliability_note' => 'دراسة عسكرية أرشيفية توثق عملية العبور. مصدر عام موثوق مؤقتًا لمرحلة الديمو، لحد ما مؤسسة تأريخ متخصصة تراجع المحتوى.',
        ]);

        $tabaSource = Source::create([
            'title' => 'Mubarak hoists Egyptian flag over Taba',
            'author' => 'UPI Archives',
            'reference' => 'https://www.upi.com/Archives/1989/03/19/Mubarak-hoists-Egyptian-flag-over-Taba/6485606286800/',
            'reliability_note' => 'تقرير وكالة أنباء معاصر لتاريخ الحدث نفسه (19 مارس 1989). مصدر موثوق أوّلي لحد ما مؤسسة تأريخ متخصصة تراجع المحتوى.',
        ]);

        $era = Era::create([
            'name_ar' => 'حرب أكتوبر واسترداد سيناء',
            'name_en' => 'The October War & the Recovery of Sinai',
            'start_year' => 1973,
            'end_year' => 1989,
            'description' => 'من عبور قناة السويس في حرب أكتوبر 1973 إلى استكمال استرداد سيناء بتحرير طابا عام 1989، بعد اتفاقيات كامب ديفيد ومعاهدة السلام والتحكيم الدولي.',
            'source_id' => $crossingSource->id,
            'status' => 'published',
        ]);

        $crossingEvent = Event::create([
            'era_id' => $era->id,
            'title_ar' => 'عبور قناة السويس - حرب أكتوبر',
            'title_en' => 'Crossing of the Suez Canal - October War',
            'event_year' => 1973,
            'event_month' => 10,
            'event_day' => 6,
            'event_hour' => 14,
            'event_minute' => 5,
            'date_precision' => 'exact',
            'ends_year' => 1973,
            'ends_month' => 10,
            'ends_day' => 6,
            'ends_hour' => 23,
            'ends_minute' => 59,
            'latitude' => 30.5965,
            'longitude' => 32.2715,
            'location_name' => 'الإسماعيلية، قناة السويس',
            'category' => 'معركة',
            'description' => 'في 6 أكتوبر 1973 عبرت القوات المسلحة المصرية قناة السويس واخترقت خط بارليف، في بداية حرب أكتوبر التي أعادت لمصر جزءًا كبيرًا من الثقة الوطنية بعد نكسة 1967، ومهّدت لاحقًا لاسترداد سيناء بالكامل.',
            'source_id' => $crossingSource->id,
            'status' => 'published',
        ]);

        EventFrame::create([
            'event_id' => $crossingEvent->id,
            'occurs_year' => 1973,
            'occurs_month' => 10,
            'occurs_day' => 6,
            'occurs_hour' => 14,
            'occurs_minute' => 5,
            'geometry_type' => 'polygon',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[[32.15, 29.90], [32.45, 29.90], [32.45, 31.35], [32.15, 31.35], [32.15, 29.90]]],
            ],
            'icon' => 'troops',
            'label' => 'بدء العبور - الساعة H (14:05)، على طول القناة من بورسعيد للسويس',
        ]);

        Event::create([
            'era_id' => $era->id,
            'title_ar' => 'تحرير طابا',
            'title_en' => 'Liberation of Taba',
            'event_year' => 1989,
            'event_month' => 3,
            'event_day' => 19,
            'date_precision' => 'exact',
            'latitude' => 29.4906,
            'longitude' => 34.8944,
            'location_name' => 'طابا، جنوب سيناء',
            'category' => 'حدث سياسي',
            'description' => 'في 19 مارس 1989 رفع الرئيس حسني مبارك العلم المصري فوق طابا، بعد انسحاب آخر جندي إسرائيلي منها في 15 مارس، عقب حكم هيئة تحكيم دولية في جنيف في 29 سبتمبر 1988 لصالح مصر بأحقيتها في المنطقة. كان هذا آخر جزء من أراضي سيناء يُستكمل استرداده.',
            'source_id' => $tabaSource->id,
            'status' => 'published',
        ]);
    }
}
