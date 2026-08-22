<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AtlasMisr') }} — خريطة مصر عبر العصور</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-stone-900 text-stone-100 antialiased">
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(circle_at_50%_0%,rgba(217,119,6,0.12),transparent_60%)]"></div>

        <header class="relative z-10 flex items-center justify-between px-6 py-5 sm:px-10">
            <span class="text-sm font-semibold tracking-wide">أطلس مصر</span>
            <a
                href="{{ route('map') }}"
                class="rounded-full bg-amber-600 px-4 py-1.5 text-xs font-semibold text-stone-950 shadow transition hover:bg-amber-500"
            >افتح الخريطة</a>
        </header>

        <main class="relative z-10">
            <!-- Hero -->
            <section class="mx-auto max-w-3xl px-6 pt-16 pb-20 text-center sm:pt-24">
                <h1 class="text-4xl font-bold leading-tight text-stone-50 sm:text-5xl">
                    خريطة مصر التاريخية،<br class="hidden sm:block"> عصرًا بعد عصر
                </h1>
                <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-stone-300">
                    منصة تفاعلية مجانية ومفتوحة المصدر، بتعرض أحداث تاريخ مصر على خريطة وتايم لاين حقيقي —
                    من بناء الأهرام لحد استرداد سيناء، بمصادر موثّقة لكل حدث.
                </p>
                <div class="mt-10 flex justify-center">
                    <a
                        href="{{ route('map') }}"
                        class="rounded-full bg-amber-600 px-8 py-3 text-base font-semibold text-stone-950 shadow-lg shadow-amber-900/30 transition hover:bg-amber-500"
                    >استكشف الخريطة</a>
                </div>
            </section>

            <!-- Principles -->
            <section class="border-t border-stone-800 bg-stone-950/40">
                <div class="mx-auto grid max-w-5xl grid-cols-1 gap-8 px-6 py-16 sm:grid-cols-2 sm:px-10 lg:grid-cols-4">
                    <div>
                        <div class="mb-3 text-2xl">🌍</div>
                        <h3 class="mb-1 font-semibold text-stone-100">مفتوح المصدر بالكامل</h3>
                        <p class="text-sm leading-6 text-stone-400">الكود متاح للجميع، لأي حد يقدر يشغّله أو يطوّره.</p>
                    </div>
                    <div>
                        <div class="mb-3 text-2xl">🗺️</div>
                        <h3 class="mb-1 font-semibold text-stone-100">بلا خرائط جوجل</h3>
                        <p class="text-sm leading-6 text-stone-400">اعتماد كامل على أدوات خرائط حرة ومفتوحة، بلا قيود أو اشتراكات.</p>
                    </div>
                    <div>
                        <div class="mb-3 text-2xl">📚</div>
                        <h3 class="mb-1 font-semibold text-stone-100">الدقة التاريخية أولاً</h3>
                        <p class="text-sm leading-6 text-stone-400">كل حدث معروض له مصدر موثّق، وواضح إن المراجعة الأكاديمية خطوة قادمة.</p>
                    </div>
                    <div>
                        <div class="mb-3 text-2xl">🆓</div>
                        <h3 class="mb-1 font-semibold text-stone-100">مجاني بالكامل</h3>
                        <p class="text-sm leading-6 text-stone-400">بلا اشتراكات، بلا إعلانات، بلا رسوم مخفية — للأبد.</p>
                    </div>
                </div>
            </section>

            <!-- About -->
            <section class="mx-auto max-w-2xl px-6 py-20 text-center sm:px-10">
                <h2 class="text-2xl font-bold text-stone-50">ليه المشروع ده؟</h2>
                <p class="mt-4 leading-8 text-stone-300">
                    مفيش أداة عربية بنفس المستوى من الدقة والتفاعل بتعرض تاريخ مصر بصريًا وجغرافيًا.
                    المشروع خدمة تعليمية ومجتمعية مبنية بالكامل من غير أي تمويل أو جهة تجارية،
                    بهدف يفضل مصدر موثوق لطلاب وباحثين ومهتمين بتاريخ مصر، من غير أي مقابل.
                </p>
            </section>
        </main>

        <footer class="relative z-10 border-t border-stone-800 px-6 py-8 text-center text-xs text-stone-500 sm:px-10">
            أطلس مصر — مشروع مفتوح المصدر غير ربحي
        </footer>
    </body>
</html>
