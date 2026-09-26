import { defineConfig } from 'vite';

/**
 * Production-style build only. `npm run dev` watches and writes hashed files
 * into public/build so PHP can keep using the same vite() helper locally
 * and on Vedos (no Node on the server).
 */
export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                editor: 'resources/js/editor.js',
            },
        },
    },
});
