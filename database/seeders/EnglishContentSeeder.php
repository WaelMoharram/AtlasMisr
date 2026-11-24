<?php

namespace Database\Seeders;

use App\Models\Era;
use App\Models\Event;
use App\Models\EventFrame;
use App\Models\Source;
use Illuminate\Database\Seeder;

/**
 * English text for the content that was first written in Arabic only.
 * Safe to run again: records are matched by their English title/name and only the *_en fields are written.
 */
class EnglishContentSeeder extends Seeder
{
    public function run(): void
    {
        $eras = [
            'The October War & the Recovery of Sinai' => 'From the crossing of the Suez Canal in the October War of 1973 to the liberation of Taba in 1989, the last piece of Sinai to return, after the Camp David Accords, the peace treaty and international arbitration.',
            'Old Kingdom' => 'The age of the great pyramids, from the rise of a strong central government in Egypt to the First Intermediate Period.',
            'The Islamic Conquest of Egypt' => 'From the conquest of Egypt by Amr ibn al-As and the founding of Fustat as its first Islamic capital, to the end of direct Umayyad rule.',
            'The Mamluk Sultanate' => 'From the rise of the Mamluk state in Egypt to its fall to the Ottomans. It stopped the Mongol advance into the region.',
            'The Era of Muhammad Ali' => 'From Muhammad Ali taking power in Egypt to the end of his rule, and the building of modern Egypt and its modern state.',
        ];
        foreach ($eras as $name => $text) {
            Era::where('name_en', $name)->update(['description_en' => $text]);
        }

        $events = [
            'Crossing of the Suez Canal - October War' => [
                'location_name_en' => 'Ismailia, Suez Canal', 'category_en' => 'Battle',
                'side_a_label_en' => 'Egyptian forces', 'side_b_label_en' => 'Israeli forces',
                'description_en' => 'On 6 October 1973 the Egyptian armed forces crossed the Suez Canal and breached the Bar Lev Line, opening the October War. It restored much of Egypt\'s national confidence after the setback of 1967 and paved the way for the full recovery of Sinai.',
            ],
            'Liberation of Taba' => [
                'location_name_en' => 'Taba, South Sinai', 'category_en' => 'Political event',
                'description_en' => 'On 19 March 1989 President Hosni Mubarak raised the Egyptian flag over Taba, after the last Israeli soldier withdrew on 15 March, following an international arbitration ruling in Geneva on 29 September 1988 in Egypt\'s favour. It was the last piece of Sinai to be recovered.',
            ],
            'Construction of the Great Pyramid' => [
                'location_name_en' => 'Giza', 'category_en' => 'Construction',
                'description_en' => 'The building of the Great Pyramid of Khufu at Giza during the reign of King Khufu (c. 2589-2566 BCE), one of the greatest achievements of architecture in the ancient world.',
            ],
            'Founding of Fustat' => [
                'location_name_en' => 'Fustat, Cairo', 'category_en' => 'Political event',
                'description_en' => 'After conquering Egypt, Amr ibn al-As founded Fustat, the first Islamic city in Egypt and in Africa, and built the Mosque of Amr there, the first mosque on the continent.',
            ],
            'Battle of Ain Jalut' => [
                'location_name_en' => 'Ain Jalut, Palestine', 'category_en' => 'Battle',
                'description_en' => 'The victory of Sultan Qutuz and his commander Baybars over the Mongol army led by Kitbuqa, a battle that halted the Mongol advance across western Asia and protected Egypt from invasion.',
            ],
            'Massacre of the Citadel' => [
                'location_name_en' => 'Saladin Citadel, Cairo', 'category_en' => 'Political event',
                'description_en' => 'Muhammad Ali invited the Mamluk leaders to a ceremony at the Citadel and, during the procession, his troops closed in and killed them, ending Mamluk influence and securing his rule over Egypt.',
            ],
            'Battle of Kilo 19' => [
                'location_name_en' => 'Lahtanit fortress, about 19 km south of Port Fuad', 'category_en' => 'Battle',
                'description_en' => 'One of the first battles of Operation Badr on 6 October 1973, around the Lahtanit fortress, one of the strongpoints of the Bar Lev Line. Its details remained little known to the wider public for a long time.',
            ],
            'Al-Mansoura Air Battle' => [
                'location_name_en' => 'The skies of Mansoura, Dakahlia', 'category_en' => 'Air battle',
                'description_en' => 'An Israeli attempt with about 120 aircraft to destroy Egyptian air force bases at Tanta, Mansoura and Salhia was met by Egyptian fighters in one of the longest air battles ever fought (about 53 minutes), and Israel failed to achieve its goals.',
            ],
        ];
        foreach ($events as $title => $fields) {
            Event::where('title_en', $title)->update($fields);
        }

        $frames = [
            'بدء العبور - الساعة H (14:05)، على طول القناة من بورسعيد للسويس' => 'Crossing begins at H-hour (14:05), along the canal from Port Said to Suez',
            'بدء العبور من الضفة الغربية' => 'Crossing begins from the west bank',
            'الوصول للضفة الشرقية' => 'Reaching the east bank',
            'تثبيت رأس الكوبري' => 'Securing the bridgehead',
            'التقدم داخل سيناء' => 'Advancing into Sinai',
            'نقاط خط بارليف' => 'Bar Lev Line strongpoints',
            'اشتباكات الدفاع الأولى' => 'First defensive clashes',
            'تراجع تكتيكي' => 'Tactical withdrawal',
            'إعادة تجمع شرق سيناء' => 'Regrouping in eastern Sinai',
            'منطقة سيطرة مصرية 14:05' => 'Egyptian zone of control 14:05',
            'منطقة سيطرة إسرائيلية 14:05' => 'Israeli zone of control 14:05',
            'منطقة سيطرة مصرية 15:00' => 'Egyptian zone of control 15:00',
            'منطقة سيطرة إسرائيلية 15:00' => 'Israeli zone of control 15:00',
            'منطقة سيطرة مصرية 17:00' => 'Egyptian zone of control 17:00',
            'منطقة سيطرة إسرائيلية 17:00' => 'Israeli zone of control 17:00',
            'منطقة سيطرة مصرية 20:00' => 'Egyptian zone of control 20:00',
            'منطقة سيطرة إسرائيلية 20:00' => 'Israeli zone of control 20:00',
        ];
        foreach ($frames as $ar => $en) {
            EventFrame::where('label', $ar)->update(['label_en' => $en]);
        }

        $sources = [
            'The Crossing of the Suez Canal, October 6 1973' => 'An archival military study documenting the crossing. A reliable public source for the demo stage, until a specialist history institution reviews the content.',
            'Mubarak hoists Egyptian flag over Taba' => 'A news-agency report written at the time of the event itself (19 March 1989). A reliable primary source until a specialist history institution reviews the content.',
            'Great Pyramid of Giza' => 'A reliable general history encyclopedia. A temporary source for the demo stage until a specialist academic review.',
            'Fustat' => 'A general public source for the demo stage, until a specialist history institution reviews the content.',
            'Battle of Ain Jalut' => 'A reliable general encyclopedia. A temporary source for the demo stage until a specialist academic review.',
            'Massacre of the Citadel' => 'A general public source for the demo stage, until a specialist history institution reviews the content.',
        ];
        foreach ($sources as $title => $note) {
            Source::where('title', $title)->update(['reliability_note_en' => $note]);
        }
        Source::where('title', 'انزال رمانة أو معركة الكيلو 19')->update([
            'title_en' => 'The Ramana landing, or the Battle of Kilo 19',
            'reliability_note_en' => 'A registered Egyptian cultural institution for history and research, documenting through the testimony of the participants themselves. A temporary source for the demo stage until an academic review.',
        ]);
        Source::where('title', 'معركة المنصورة الجوية')->update([
            'title_en' => 'The Air Battle of Mansoura',
            'reliability_note_en' => 'A registered Egyptian cultural institution for history and research, documenting through the testimony of the participants themselves (here, the pilot Abdel Moneim Hammam). A temporary source for the demo stage until an academic review.',
        ]);
    }
}
