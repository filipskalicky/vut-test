<?php

/**
 * Vite asset helper.
 *
 * Reads the production manifest written by `npm run build` and emits
 * <link> / <script> tags. Vite 5 stores the manifest under public/build/.vite/.
 */
if (! function_exists('vite')) {
    /**
     * Render HTML tags for a Vite entry (JS and its imported CSS).
     *
     * @param string $entry Path used as the Rollup input key, e.g. resources/js/app.js
     */
    function vite(string $entry): string
    {
        $manifest = vite_manifest();

        if ($manifest === []) {
            return "<!-- Vite manifest not found. Run: npm run build -->\n";
        }

        if (! isset($manifest[$entry])) {
            return '<!-- Vite entry not found: ' . esc($entry) . " -->\n";
        }

        $tags  = '';
        $chunk = $manifest[$entry];

        foreach ($chunk['css'] ?? [] as $cssFile) {
            $tags .= '<link rel="stylesheet" href="' . esc(base_url('build/' . $cssFile)) . '">' . PHP_EOL;
        }

        if (isset($chunk['file'])) {
            $tags .= '<script type="module" src="' . esc(base_url('build/' . $chunk['file'])) . '"></script>' . PHP_EOL;
        }

        return $tags;
    }
}

if (! function_exists('vite_manifest')) {
    /**
     * @return array<string, array<string, mixed>>
     */
    function vite_manifest(): array
    {
        static $manifest = null;

        if ($manifest !== null) {
            return $manifest;
        }

        $candidates = [
            FCPATH . 'build/.vite/manifest.json',
            FCPATH . 'build/manifest.json',
        ];

        foreach ($candidates as $path) {
            if (! is_file($path)) {
                continue;
            }

            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $manifest = $decoded;

                return $manifest;
            }
        }

        $manifest = [];

        return $manifest;
    }
}
