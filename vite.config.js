import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-map-picker.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        rollupOptions: {
            output: {
                // Some servers (e.g. Valet's default nginx mime.types) don't
                // map .mjs to a JS content type, which browsers reject for
                // module scripts. Emit worker/asset scripts as .js instead,
                // since that extension is universally recognized.
                assetFileNames: (assetInfo) => {
                    const name = assetInfo.name ?? assetInfo.names?.[0] ?? '';

                    if (name.endsWith('.mjs')) {
                        return 'assets/[name]-[hash].js';
                    }

                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
});
