import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-editor.js',
            ],
            refresh: true,
            fonts: [
                bunny('Playfair Display', {
                    weights: [400, 600],
                    subsets: ['latin', 'latin-ext'],
                }),
                bunny('Source Sans 3', {
                    weights: [400, 600],
                    subsets: ['latin', 'latin-ext'],
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
});
