# أطلس مصر (AtlasMisr)

خريطة تاريخية تفاعلية لمصر عبر العصور — منصة ويب مجانية ومفتوحة المصدر بالكامل، بتعرض أحداث تاريخية على خريطة وتايم لاين تفاعلي، بمصداقية موثقة بمصادر.

هذا مشروع هواية غير ربحي، الهدف منه بناء ديمو شغال يُعرض لاحقًا على مؤسسات تأريخ كنقطة انطلاق لتعاون حقيقي. راجع [atlasmisr-project-plan.md](atlasmisr-project-plan.md) للخطة الكاملة.

## الستاك

| الطبقة | الاختيار |
|---|---|
| الخريطة | [MapLibre GL JS](https://maplibre.org/) + [OpenFreeMap](https://openfreemap.org/) tiles — بلا Google Maps، بلا API keys |
| Backend | Laravel 13 |
| قاعدة البيانات | MySQL 8 |
| لوحة التحكم | [Filament](https://filamentphp.com/) |
| Frontend | Alpine.js + Tailwind CSS |

## التشغيل محليًا

المتطلبات: PHP 8.4+، Composer، MySQL/MariaDB، Node.js 18+.

```bash
git clone <repo-url> atlasmisr
cd atlasmisr

composer install
npm install

cp .env.example .env
php artisan key:generate
```

عدّل بيانات قاعدة البيانات في `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)، وأنشئ قاعدة بيانات فاضية بنفس الاسم، بعدين:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build

php artisan serve
```

الموقع هيشتغل على `http://127.0.0.1:8000`. المايجريشن + السييدر هيدخلوا الحقبة والحدثين الأساسيين للديمو تلقائيًا.

### لوحة التحكم

عشان تدخل على `/admin`، اعمل يوزر admin:

```bash
php artisan make:filament-user
```

## البنية

- `app/Models` — `Era`, `Event`, `Source`
- `app/Filament/Resources` — لوحة تحكم الـ CRUD
- `app/Http/Controllers/Api` + `routes/api.php` — `GET /api/eras`, `GET /api/events?era_id=&bbox=`
- `resources/js/app.js` — منطق الخريطة والتايم لاين (Alpine + MapLibre)
- `resources/views/welcome.blade.php` — الصفحة الرئيسية
- `database/seeders/DemoContentSeeder.php` — بيانات الديمو (حرب أكتوبر وتحرير طابا)

## المساهمة

مرحّب بأي مساهمة: اقتراح حدث أو مصدر، تصحيح، أو كود. التفاصيل في [CONTRIBUTING.md](CONTRIBUTING.md) (عربي وإنجليزي).

## الترخيص

هيتحدد لاحقًا (MIT أو AGPL مقترحين — راجع خطة المشروع).
