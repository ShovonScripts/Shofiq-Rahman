import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // ✅ Separate vendor chunks for better caching
                manualChunks: {
                    vue: ['vue', '@inertiajs/vue3', '@inertiajs/progress'],
                    bootstrap: ['bootstrap', '@popperjs/core'],
                    ziggy: ['ziggy-js'],
                    markdown: ['markdown-it', 'dompurify', 'marked'],
                },
            },
        },
    },
});
