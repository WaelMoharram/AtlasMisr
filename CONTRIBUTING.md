# المساهمة في أطلس مصر / Contributing to AtlasMisr

أطلس مصر مشروع مجاني ومفتوح المصدر وغير ربحي. أي مساهمة صغيرة (حدث، مصدر، تصحيح، سطر كود) بتفرق.
AtlasMisr is a free, open-source, non-profit project. Every small contribution helps: an event, a source, a correction, a line of code.

## المبادئ / Principles

1. **كل حدث له مصدر.** من غير مصدر مش هيتضاف. / **Every event needs a source.** No source, no entry.
2. **الدقة التاريخية أولاً.** لو المعلومة مختلف عليها، اذكر ده. / **Accuracy first.** If something is disputed, say so.
3. **بلا خرائط جوجل ولا مفاتيح API.** بنستخدم أدوات مفتوحة (MapLibre وOpenFreeMap). / **No Google Maps, no API keys.** We use open tools.
4. **مجاني بالكامل:** بلا إعلانات ولا اشتراكات. / **Free for good:** no ads, no subscriptions.

## اقترح حدث أو مصدر / Suggest an event or a source

مش لازم تكون مبرمج. افتح [Issue بنموذج "اقتراح حدث"](../../issues/new?template=event-suggestion.md) أو ابعت لنا على mail@waelmoharram.com، وحط التفاصيل دي:
You do not need to be a developer. Open an [event suggestion issue](../../issues/new?template=event-suggestion.md) or email mail@waelmoharram.com with:

| الحقل / Field | مثال / Example |
|---|---|
| العنوان بالعربي والإنجليزي / Title (Arabic and English) | تحرير طابا / Liberation of Taba |
| التاريخ / Date | 19/3/1989 (قبل الميلاد بسالب / BCE as a minus: -2560) |
| المكان / Place | طابا، جنوب سيناء (+ إحداثيات لو عندك / coordinates if you have them) |
| وصف قصير / Short description | 2-4 جمل / 2-4 sentences |
| المصدر / Source | العنوان والمؤلف والرابط / title, author, link |
| فيديو (اختياري) / Video (optional) | رابط يوتيوب / YouTube link |

الأحداث بتتضاف حالياً من لوحة التحكم بواسطة مسؤول المشروع، وبعد مراجعة المصدر.
Events are added through the admin dashboard by the maintainer, after the source is checked.

## ساهم بالكود / Contribute code

1. اعمل Fork وClone، وشغّل المشروع حسب [README](README.md). / Fork, clone, and run the project as described in the [README](README.md).
2. اعمل branch جديد باسم واضح، مثلاً `fix-timeline-label`. / Create a branch with a clear name.
3. حافظ على التغيير صغير ومركّز، وغيّر حاجة واحدة في كل Pull Request. / Keep changes small and focused.
4. نصوص الواجهة في `lang/ar/atlas.php` و`lang/en/atlas.php` (الاتنين مع بعض). / UI text lives in `lang/ar/atlas.php` and `lang/en/atlas.php` (update both).
5. أي تغيير في قاعدة البيانات يتم عن طريق migration جديدة. / Database changes go through a new migration.
6. قبل ما تبعت: / Before you send:

   ```bash
   php artisan test
   ./vendor/bin/pint
   ```

7. افتح Pull Request واشرح إيه اللي اتغيّر وليه. / Open a pull request and explain what changed and why.

## الإبلاغ عن مشكلة / Report a problem

افتح Issue واكتب إيه اللي حصل، وإيه اللي كنت متوقعه، والمتصفح اللي استخدمته.
Open an issue: what happened, what you expected, and which browser you used.

شكراً لوقتك. / Thank you for your time.
