<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AtlasMisr') }} — خريطة مصر عبر العصور</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-screen w-screen overflow-hidden bg-stone-900 text-stone-100 antialiased">
        <div x-data="atlas" class="relative h-full w-full">
            <div x-ref="map" class="absolute inset-0" style="position: absolute; inset: 0;"></div>

            <header class="pointer-events-none absolute inset-x-0 top-0 z-10 flex items-start justify-between gap-3 p-4">
                <!-- Search -->
                <div class="pointer-events-auto relative w-full max-w-[220px] sm:max-w-xs" @click.outside="searchOpen = false">
                    <input
                        type="search"
                        x-model="searchQuery"
                        @focus="searchOpen = true"
                        @input="searchOpen = true"
                        placeholder="ابحث عن حدث..."
                        class="w-full rounded-full border border-stone-700 bg-stone-900/85 px-4 py-2 text-sm text-stone-100 placeholder-stone-500 shadow-lg backdrop-blur focus:border-amber-500 focus:outline-none"
                    >
                    <template x-if="searchOpen && searchQuery.trim() !== ''">
                        <ul class="absolute mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-stone-700 bg-stone-900/95 shadow-xl backdrop-blur">
                            <template x-for="result in searchResults" :key="result.id">
                                <li>
                                    <button
                                        type="button"
                                        @click="goToEvent(result)"
                                        class="block w-full px-4 py-2 text-start text-sm text-stone-200 hover:bg-stone-800"
                                    >
                                        <span class="block" x-text="result.title_ar"></span>
                                        <span class="block text-xs text-stone-500" x-text="formatEventDate(result)"></span>
                                    </button>
                                </li>
                            </template>
                            <template x-if="searchResults.length === 0">
                                <li class="px-4 py-3 text-sm text-stone-500">مفيش نتائج</li>
                            </template>
                        </ul>
                    </template>
                </div>

                <div class="pointer-events-auto shrink-0 rounded-full bg-stone-900/80 px-5 py-2 text-center shadow-lg backdrop-blur">
                    <h1 class="text-sm font-semibold tracking-wide">أطلس مصر</h1>
                </div>

                <a
                    href="{{ route('landing') }}"
                    class="pointer-events-auto hidden shrink-0 self-center rounded-full bg-stone-900/80 px-4 py-2 text-xs text-stone-300 shadow-lg backdrop-blur hover:bg-stone-800 sm:block"
                >الرئيسية</a>
            </header>

            <template x-if="loading">
                <div class="absolute inset-0 z-20 flex items-center justify-center bg-stone-900/60">
                    <p class="text-sm text-stone-300">جاري تحميل الخريطة...</p>
                </div>
            </template>

            <template x-if="error">
                <div class="absolute inset-0 z-20 flex items-center justify-center bg-stone-900/60">
                    <p class="rounded-lg bg-red-950/80 px-4 py-2 text-sm text-red-200" x-text="error"></p>
                </div>
            </template>

            <!-- Event detail panel -->
            <aside
                x-show="selectedEvent"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                class="absolute inset-y-0 end-0 z-20 w-full max-w-sm overflow-y-auto bg-stone-900/95 p-5 shadow-2xl backdrop-blur"
            >
                <template x-if="selectedEvent">
                    <div>
                        <button
                            type="button"
                            @click="closePanel()"
                            class="mb-4 rounded-full bg-stone-800 px-3 py-1 text-xs text-stone-300 hover:bg-stone-700"
                        >
                            إغلاق ✕
                        </button>

                        <span
                            class="mb-2 inline-block rounded-full bg-amber-900/60 px-3 py-1 text-xs text-amber-200"
                            x-text="selectedEvent.category"
                        ></span>

                        <h2 class="text-xl font-bold text-stone-50" x-text="selectedEvent.title_ar"></h2>
                        <p class="mt-1 text-sm text-stone-400" x-text="formatEventDate(selectedEvent)"></p>
                        <p class="mt-1 text-sm text-stone-400" x-text="selectedEvent.location_name"></p>

                        <p class="mt-4 leading-7 text-stone-200" x-text="selectedEvent.description"></p>

                        <template x-if="selectedEvent.media && selectedEvent.media.length">
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <template x-for="src in selectedEvent.media" :key="src">
                                    <img :src="src" class="aspect-square w-full rounded-lg object-cover" alt="">
                                </template>
                            </div>
                        </template>

                        <template x-if="youtubeEmbedUrl(selectedEvent.video_url)">
                            <div class="mt-4 aspect-video w-full overflow-hidden rounded-lg">
                                <iframe
                                    :src="youtubeEmbedUrl(selectedEvent.video_url)"
                                    class="h-full w-full"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </template>

                        <template x-if="hasSideAnimation(selectedEvent)">
                            <div class="mt-4 rounded-xl border border-stone-700 bg-stone-800/60 p-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-stone-300">خريطة القوات المتحركة</span>
                                    <button
                                        type="button"
                                        @click="playbackActive ? stopPlayback() : startPlayback(selectedEvent)"
                                        class="rounded-full bg-amber-600 px-3 py-1 text-xs font-bold text-stone-950 hover:bg-amber-500"
                                        x-text="playbackActive ? 'إيقاف ⏸' : 'تشغيل ▶'"
                                    ></button>
                                </div>

                                <p
                                    class="mb-2 text-center font-mono text-xs text-amber-300"
                                    x-show="playbackDisplay.momentLabel"
                                    x-text="playbackDisplay.momentLabel"
                                ></p>

                                <div class="grid grid-cols-2 gap-2 text-center text-xs">
                                    <div class="rounded-lg bg-red-950/50 p-2">
                                        <p class="font-semibold text-red-300" x-text="selectedEvent.side_a_label || 'جانب أ'"></p>
                                        <p class="mt-1 text-stone-400">
                                            قوات:
                                            <span
                                                class="font-mono font-bold text-red-200"
                                                x-text="playbackDisplay.a?.troop_count != null ? playbackDisplay.a.troop_count.toLocaleString('ar-EG') : '—'"
                                            ></span>
                                        </p>
                                        <p class="text-stone-400">
                                            خسائر:
                                            <span
                                                class="font-mono font-bold text-red-200"
                                                x-text="playbackDisplay.a?.casualties != null ? playbackDisplay.a.casualties.toLocaleString('ar-EG') : '—'"
                                            ></span>
                                        </p>
                                    </div>
                                    <div class="rounded-lg bg-blue-950/50 p-2">
                                        <p class="font-semibold text-blue-300" x-text="selectedEvent.side_b_label || 'جانب ب'"></p>
                                        <p class="mt-1 text-stone-400">
                                            قوات:
                                            <span
                                                class="font-mono font-bold text-blue-200"
                                                x-text="playbackDisplay.b?.troop_count != null ? playbackDisplay.b.troop_count.toLocaleString('ar-EG') : '—'"
                                            ></span>
                                        </p>
                                        <p class="text-stone-400">
                                            خسائر:
                                            <span
                                                class="font-mono font-bold text-blue-200"
                                                x-text="playbackDisplay.b?.casualties != null ? playbackDisplay.b.casualties.toLocaleString('ar-EG') : '—'"
                                            ></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedEvent.source">
                            <div class="mt-6 border-t border-stone-700 pt-4 text-xs text-stone-400">
                                <p class="mb-1 font-semibold text-stone-300">المصدر</p>
                                <p x-text="selectedEvent.source.title"></p>
                                <a
                                    :href="selectedEvent.source.reference"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-amber-400 hover:underline"
                                    x-text="selectedEvent.source.reference"
                                ></a>
                            </div>
                        </template>
                    </div>
                </template>
            </aside>

            <!-- Timeline -->
            <template x-if="timelineMoments.length > 0">
                <footer class="pointer-events-none absolute inset-x-0 bottom-0 z-10 flex justify-center p-4">
                    <div class="pointer-events-auto w-full max-w-xl rounded-2xl bg-stone-900/85 p-4 shadow-lg backdrop-blur">
                        <div class="mb-2 flex items-center justify-between text-xs text-stone-300">
                            <span x-text="currentEra ? currentEra.name_ar : 'بين العصور'"></span>
                            <span class="font-mono text-amber-300" x-text="formatMoment(moment)"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="stepTime(-1)"
                                :disabled="momentIndex <= 0"
                                class="shrink-0 rounded-full bg-stone-800 px-2.5 py-1 text-stone-300 hover:bg-stone-700 disabled:opacity-30"
                                aria-label="السابق"
                            >▶</button>
                            <input
                                type="range"
                                class="w-full accent-amber-500"
                                min="0"
                                :max="timelineMoments.length - 1"
                                step="1"
                                x-model.number="momentIndex"
                                @input="onTimeChange()"
                            >
                            <button
                                type="button"
                                @click="stepTime(1)"
                                :disabled="momentIndex >= timelineMoments.length - 1"
                                class="shrink-0 rounded-full bg-stone-800 px-2.5 py-1 text-stone-300 hover:bg-stone-700 disabled:opacity-30"
                                aria-label="التالي"
                            >◀</button>
                        </div>
                        <div class="mt-1 flex justify-between text-[11px] text-stone-500">
                            <span x-text="formatMoment(timelineMoments[0])"></span>
                            <span x-text="formatMoment(timelineMoments[timelineMoments.length - 1])"></span>
                        </div>
                    </div>
                </footer>
            </template>
        </div>
    </body>
</html>
