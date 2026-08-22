<div x-data="mapGeometryPicker()" wire:ignore class="space-y-2">
    <div class="flex gap-2">
        <button
            type="button"
            @click="setMode('point')"
            :class="mode === 'point' ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
            class="rounded-md px-3 py-1 text-xs font-medium"
        >نقطة</button>
        <button
            type="button"
            @click="setMode('polygon')"
            :class="mode === 'polygon' ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
            class="rounded-md px-3 py-1 text-xs font-medium"
        >بوليجون</button>

        <template x-if="mode === 'polygon'">
            <div class="flex gap-2">
                <button type="button" @click="undoLast()" class="rounded-md bg-gray-100 dark:bg-gray-800 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300">تراجع عن آخر نقطة</button>
                <button type="button" @click="clear()" class="rounded-md bg-gray-100 dark:bg-gray-800 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300">مسح الكل</button>
            </div>
        </template>
    </div>

    <div x-ref="map" style="height: 320px; border-radius: 0.5rem; overflow: hidden;"></div>

    <p class="text-xs text-gray-500 dark:text-gray-400" x-show="mode === 'point'">دوس على الخريطة لتحديد نقطة، أو اسحب العلامة لتعديلها.</p>
    <p class="text-xs text-gray-500 dark:text-gray-400" x-show="mode === 'polygon'">دوس على الخريطة عشان تضيف زوايا البوليجون بالترتيب (٣ نقاط على الأقل).</p>
</div>
