<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}" class="scroll-smooth">
<head>
    @include('partials.head')
    @vite(['resources/css/app.css'])
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => __('atlas.name'),
        'url' => route($lang.'.landing'),
        'inLanguage' => $lang,
        'description' => __('atlas.description'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body class="bg-night font-sans text-stone-200 antialiased selection:bg-gold/30">

{{-- ============ Header ============ --}}
<header class="absolute inset-x-0 top-0 z-30">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
        <a href="{{ route($lang.'.landing') }}" class="flex items-center gap-2.5">
            <svg viewBox="0 0 32 32" class="size-8" aria-hidden="true">
                <circle cx="16" cy="16" r="14" fill="none" stroke="#d9a441" stroke-width="1.5"/>
                <circle cx="16" cy="12" r="4.2" fill="#d9a441"/>
                <path d="M3 22c4-3 8-3 13 0s9 3 13 0" fill="none" stroke="#2c7a80" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="font-display text-2xl font-bold tracking-wide text-stone-50">{{ __('atlas.name') }}</span>
        </a>
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route($otherLang.'.landing') }}" hreflang="{{ $otherLang }}" lang="{{ $otherLang }}" class="rounded-full px-3 py-1.5 text-stone-300 transition hover:bg-white/10 hover:text-white">{{ __('atlas.other_lang_name') }}</a>
            <a href="{{ route($lang.'.map') }}" class="rounded-full bg-gold px-4 py-2 font-semibold text-night shadow-lg shadow-gold/20 transition hover:bg-gold-soft">{{ __('atlas.nav_map') }}</a>
        </nav>
    </div>
</header>

<main>
{{-- ============ Hero ============ --}}
<section class="relative isolate flex min-h-[92vh] items-end overflow-hidden grain">
    @if ($hasHero)
        <img src="{{ asset('images/hero.webp') }}" alt="" class="drift absolute inset-0 -z-20 h-full w-full object-cover" fetchpriority="high">
    @else
        <div class="absolute inset-0 -z-20 bg-[radial-gradient(ellipse_at_70%_20%,#3a2a12_0%,#0b0a0f_60%)]"></div>
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-night via-night/55 to-night/0"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-night/80 via-night/30 to-transparent rtl:bg-gradient-to-l"></div>

    <div class="mx-auto w-full max-w-6xl px-5 pb-16 pt-40 sm:px-8 sm:pb-24">
        <p class="rise inline-flex items-center gap-2 rounded-full border border-gold/30 bg-night/50 px-4 py-1.5 text-xs font-medium tracking-wide text-gold-soft backdrop-blur">
            <span class="size-1.5 rounded-full bg-gold"></span> {{ __('atlas.hero_eyebrow') }}
        </p>
        <h1 class="rise rise-2 font-display mt-6 max-w-3xl text-5xl leading-[1.1] font-bold text-stone-50 sm:text-7xl {{ $lang === 'ar' ? 'sm:leading-[1.35]' : '' }}">
            {{ __('atlas.hero_title_a') }}<br>
            <span class="text-gold">{{ __('atlas.hero_title_b') }}</span>
        </h1>
        <p class="rise rise-3 mt-6 max-w-xl text-lg leading-8 text-stone-300">{{ __('atlas.hero_text') }}</p>
        <div class="rise rise-4 mt-9 flex flex-wrap items-center gap-3">
            <a href="{{ route($lang.'.map') }}" class="rounded-full bg-gold px-8 py-3.5 text-base font-semibold text-night shadow-xl shadow-gold/25 transition hover:-translate-y-0.5 hover:bg-gold-soft">{{ __('atlas.hero_cta') }}</a>
            <a href="#how" class="rounded-full border border-white/20 px-7 py-3.5 text-base text-stone-100 backdrop-blur transition hover:bg-white/10">{{ __('atlas.hero_cta_2') }}</a>
        </div>
        <dl class="rise rise-4 mt-14 flex flex-wrap gap-x-10 gap-y-4 border-t border-white/10 pt-6">
            @foreach (['eras', 'events', 'sources'] as $k)
                <div>
                    <dd class="font-display text-4xl font-bold text-stone-50">{{ number_format($stats[$k]) }}</dd>
                    <dt class="text-sm text-stone-400">{{ __('atlas.stat_'.$k) }}</dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- ============ Eras ============ --}}
<section class="relative border-t border-white/5 bg-night py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <p class="text-sm font-medium tracking-widest text-gold uppercase">{{ __('atlas.eras_kicker') }}</p>
        <h2 class="font-display mt-3 text-4xl font-bold text-stone-50 sm:text-5xl">{{ __('atlas.eras_title') }}</h2>
        <p class="mt-4 max-w-2xl text-stone-400">{{ __('atlas.eras_text') }}</p>
    </div>

    <div class="mx-auto mt-12 max-w-6xl">
        <ol class="flex snap-x snap-mandatory gap-5 overflow-x-auto px-5 pb-6 [scrollbar-width:thin] sm:px-8 lg:grid lg:grid-cols-5 lg:overflow-visible">
            @foreach ($eras as $era)
                @php
                    $name = $lang === 'ar' ? $era->name_ar : ($era->name_en ?: $era->name_ar);
                    $desc = $lang === 'ar' ? $era->description : ($era->description_en ?: $era->description);
                    $fmt = fn ($y) => $y < 0 ? abs($y).' '.__('atlas.js.bce') : $y.($lang === 'ar' ? ' م' : '');
                @endphp
                <li class="w-64 shrink-0 snap-start lg:w-auto">
                    <a href="{{ route($lang.'.map') }}?era={{ $era->id }}" class="group flex h-full flex-col rounded-2xl border border-white/10 bg-gradient-to-b from-white/[0.06] to-transparent p-5 transition hover:-translate-y-1 hover:border-gold/50">
                        <span class="font-display text-2xl leading-tight font-bold text-gold-soft">{{ $fmt($era->start_year) }}<span class="mx-1 text-stone-500">–</span>{{ $fmt($era->end_year) }}</span>
                        <h3 class="mt-3 text-lg leading-snug font-semibold text-stone-50">{{ $name }}</h3>
                        <p class="mt-2 line-clamp-4 text-sm leading-6 text-stone-400">{{ $desc }}</p>
                        <span class="mt-auto pt-5 text-xs text-gold">{{ __('atlas.era_events', ['n' => $era->events_count]) }} <span class="inline-block rtl:-scale-x-100">&rarr;</span></span>
                    </a>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ============ Stories ============ --}}
@if ($stories->isNotEmpty())
<section class="border-t border-white/5 bg-[#0f0d12] py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <p class="text-sm font-medium tracking-widest text-gold uppercase">{{ __('atlas.events_kicker') }}</p>
        <h2 class="font-display mt-3 text-4xl font-bold text-stone-50 sm:text-5xl">{{ __('atlas.events_title') }}</h2>
        <p class="mt-4 max-w-2xl text-stone-400">{{ __('atlas.events_text') }}</p>

        <div class="mt-12 grid gap-6 md:grid-cols-6">
            @foreach ($stories as $i => $e)
                @php
                    $title = $lang === 'ar' ? $e->title_ar : ($e->title_en ?: $e->title_ar);
                    $where = $lang === 'ar' ? $e->location_name : ($e->location_name_en ?: $e->location_name);
                    $text = $lang === 'ar' ? $e->description : ($e->description_en ?: $e->description);
                    $span = $i === 0 ? 'md:col-span-6 md:grid md:grid-cols-2' : ($stories->count() === 4 && $i === 3 ? 'md:col-span-6 md:grid md:grid-cols-2' : 'md:col-span-3');
                @endphp
                <a href="{{ route($lang.'.map') }}?event={{ $e->id }}" class="group relative overflow-hidden rounded-3xl border border-white/10 bg-night {{ $span }}">
                    <div class="aspect-[3/2] overflow-hidden {{ str_contains($span, 'grid') ? 'md:aspect-auto md:min-h-[22rem]' : '' }}">
                        <img src="{{ asset('images/events/'.$e->id.'.webp') }}" alt="{{ $title }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                    </div>
                    <div class="flex flex-col justify-center p-6 sm:p-8">
                        <p class="text-xs text-gold" dir="auto">
                            {{ $e->event_year < 0 ? abs($e->event_year).' '.__('atlas.js.bce') : $e->event_year }}
                            @if ($where) · {{ $where }} @endif
                            @if ($e->frames()->exists()) · <span class="rounded-full bg-gold/15 px-2 py-0.5">{{ __('atlas.event_playback') }}</span> @endif
                        </p>
                        <h3 class="font-display mt-3 text-3xl leading-tight font-bold text-stone-50">{{ $title }}</h3>
                        <p class="mt-3 line-clamp-3 text-sm leading-7 text-stone-400">{{ $text }}</p>
                        <span class="mt-5 text-sm font-medium text-gold">{{ __('atlas.event_open') }} <span class="inline-block rtl:-scale-x-100">&rarr;</span></span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ How it works ============ --}}
<section id="how" class="scroll-mt-8 border-t border-white/5 bg-night py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <p class="text-sm font-medium tracking-widest text-gold uppercase">{{ __('atlas.how_kicker') }}</p>
        <h2 class="font-display mt-3 max-w-2xl text-4xl font-bold text-stone-50 sm:text-5xl">{{ __('atlas.how_title') }}</h2>

        @if ($hasMapShot)
            <div class="mt-12 overflow-hidden rounded-2xl border border-white/10 bg-black shadow-2xl shadow-black/50">
                <div class="flex items-center gap-1.5 border-b border-white/10 bg-white/5 px-4 py-2.5">
                    <span class="size-2.5 rounded-full bg-white/20"></span><span class="size-2.5 rounded-full bg-white/20"></span><span class="size-2.5 rounded-full bg-white/20"></span>
                    <span class="mx-auto text-xs text-stone-500" dir="ltr">atlas.waelmoharram.com/map</span>
                </div>
                <img src="{{ asset('images/map-preview.webp') }}" alt="" loading="lazy" class="block w-full">
            </div>
        @endif

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('atlas.how_items') as $item)
                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">
                    <div class="mb-4 grid size-11 place-items-center rounded-xl bg-gold/15 text-gold">
                        @switch($item['icon'])
                            @case('map') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2Zm0 0v14m6-12v14"/></svg> @break
                            @case('time') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> @break
                            @case('play') <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m10 8.5 5 3.5-5 3.5Z" fill="currentColor"/></svg> @break
                            @default <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h11a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3V4Zm0 13a3 3 0 0 1 3-3h11"/></svg>
                        @endswitch
                    </div>
                    <h3 class="font-semibold text-stone-50">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-stone-400">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ Principles ============ --}}
<section class="border-t border-white/5 bg-[#0f0d12] py-20">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <h2 class="font-display text-3xl font-bold text-stone-50 sm:text-4xl">{{ __('atlas.principles_title') }}</h2>
        <div class="mt-10 grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('atlas.principles') as $i => $p)
                <div class="border-t-2 border-gold/60 pt-4">
                    <span class="font-display text-sm text-gold">0{{ $i + 1 }}</span>
                    <h3 class="mt-1 font-semibold text-stone-50">{{ $p['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-stone-400">{{ $p['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ Call to action ============ --}}
<section class="relative isolate overflow-hidden border-t border-white/5 py-24 text-center">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_50%_100%,rgba(217,164,65,.22),transparent_65%)]"></div>
    <h2 class="font-display mx-auto max-w-2xl px-5 text-5xl font-bold text-stone-50">{{ __('atlas.cta_title') }}</h2>
    <p class="mt-4 text-stone-400">{{ __('atlas.cta_text') }}</p>
    <a href="{{ route($lang.'.map') }}" class="mt-8 inline-block rounded-full bg-gold px-9 py-3.5 font-semibold text-night shadow-xl shadow-gold/25 transition hover:-translate-y-0.5 hover:bg-gold-soft">{{ __('atlas.hero_cta') }}</a>
</section>
</main>

<footer class="border-t border-white/5 px-5 py-8 text-center text-xs text-stone-500">
    <p>{{ __('atlas.footer_line') }}</p>
    <p class="mt-2">
        {{ __('atlas.built_by') }} <a href="https://waelmoharram.com{{ $lang === 'ar' ? '/ar' : '' }}" class="text-stone-300 underline-offset-2 hover:underline">{{ $lang === 'ar' ? 'وائل محرم' : 'Wael Moharram' }}</a>
        · <a href="https://github.com/WaelMoharram/AtlasMisr" class="text-stone-300 underline-offset-2 hover:underline" rel="noopener">{{ __('atlas.source_code') }}</a>
    </p>
</footer>
</body>
</html>
