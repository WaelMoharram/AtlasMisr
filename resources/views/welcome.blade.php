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

            <header class="pointer-events-none absolute inset-x-0 top-0 z-10 flex justify-center p-4">
                <div class="pointer-events-auto rounded-full bg-stone-900/80 px-5 py-2 text-center shadow-lg backdrop-blur">
                    <h1 class="text-sm font-semibold tracking-wide">أطلس مصر</h1>
                </div>
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
            <template x-if="era">
                <footer class="pointer-events-none absolute inset-x-0 bottom-0 z-10 flex justify-center p-4">
                    <div class="pointer-events-auto w-full max-w-xl rounded-2xl bg-stone-900/85 p-4 shadow-lg backdrop-blur">
                        <div class="mb-2 flex items-center justify-between text-xs text-stone-300">
                            <span x-text="era.name_ar"></span>
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
                            <span x-text="formatYear(era.start_year)"></span>
                            <span x-text="formatYear(era.end_year)"></span>
                        </div>
                    </div>
                </footer>
            </template>
        </div>
    </body>
</html>
