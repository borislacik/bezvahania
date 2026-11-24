import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/admin/theme.css'],
            refresh: true,
            // latin-ext: Slovak letters (č, š, ľ, ť, ž, ň, ď, ĺ, ŕ, ô…) are not in the default latin subset.
            fonts: [
                bunny('Work Sans', {
                    weights: [400, 500, 600],
                    subsets: ['latin', 'latin-ext'],
                }),
                bunny('Space Grotesk', {
                    weights: [500, 600, 700],
                    subsets: ['latin', 'latin-ext'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        hmr: { host: 'localhost' },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
