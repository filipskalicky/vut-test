<?php

namespace App\Libraries;

/**
 * Turns stored Editor.js JSON into safe HTML for the public and admin views.
 *
 * Only paragraph and list blocks are rendered. Ordered lists keep start and
 * counter type. Inline markup is reduced to a whitelist
 * (b, i, strong, em, mark, a[href,target,rel]). Unknown blocks and dangerous
 * attributes are dropped so a crafted payload cannot inject scripts or images.
 */
class EditorJsRenderer
{
    /**
     * Tags that may survive inline sanitization.
     */
    private const ALLOWED_TAGS = ['b', 'i', 'strong', 'em', 'mark', 'a'];

    /**
     * @var list<string>
     */
    private const ALLOWED_TARGETS = ['_blank', '_self', '_parent', '_top'];

    /**
     * @var list<string>
     */
    private const ALLOWED_RELS = ['nofollow', 'noopener', 'noreferrer', 'external'];

    /**
     * Editor.js ordered-list counter types mapped to CSS list-style-type.
     *
     * @var array<string, string>
     */
    private const COUNTER_TYPES = [
        'numeric'      => 'decimal',
        'lower-roman'  => 'lower-roman',
        'upper-roman'  => 'upper-roman',
        'lower-alpha'  => 'lower-alpha',
        'upper-alpha'  => 'upper-alpha',
    ];

    /**
     * Render a stored Editor.js document.
     */
    public function render(?string $json): string
    {
        if ($json === null || $json === '') {
            return '';
        }

        $document = json_decode($json, true);
        if (! is_array($document) || ! isset($document['blocks']) || ! is_array($document['blocks'])) {
            return '';
        }

        $parts = [];

        foreach ($document['blocks'] as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            $html = match ($type) {
                'paragraph' => $this->renderParagraph($data),
                'list'      => $this->renderList($data),
                default     => '',
            };

            if ($html !== '') {
                $parts[] = $html;
            }
        }

        return implode('', $parts);
    }

    /**
     * Plain-text excerpt for meta descriptions. Tags and extra whitespace
     * are stripped; the result is cut on a word boundary.
     */
    public function plainText(?string $json, int $maxLength = 160): string
    {
        $text = html_entity_decode(strip_tags($this->render($json)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        if ($text === '' || $maxLength < 1 || mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $cut   = mb_substr($text, 0, $maxLength);
        $space = mb_strrpos($cut, ' ');

        if ($space !== false && $space > (int) ($maxLength * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " \t\n\r.,;:") . '…';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderParagraph(array $data): string
    {
        $text = $this->sanitizeInline((string) ($data['text'] ?? ''));

        if (trim(strip_tags($text)) === '' && $text === '') {
            return '';
        }

        return '<p>' . $text . '</p>';
    }

    /**
     * Supports both the legacy string[] list format and nested {content, items}.
     * Ordered lists keep Editor.js start index and counter type (roman, alpha).
     *
     * @param array<string, mixed> $data
     */
    private function renderList(array $data): string
    {
        $listStyle = ($data['style'] ?? 'unordered') === 'ordered' ? 'ordered' : 'unordered';
        $tag       = $listStyle === 'ordered' ? 'ol' : 'ul';
        $items     = is_array($data['items'] ?? null) ? $data['items'] : [];
        $meta      = is_array($data['meta'] ?? null) ? $data['meta'] : [];

        $inner = $this->renderListItems($items, $listStyle, $meta);

        if ($inner === '') {
            return '';
        }

        $attributes = [];

        if ($listStyle === 'ordered') {
            $start = $this->listStart($meta);
            if ($start !== null) {
                $attributes[] = 'start="' . $start . '"';
            }

            $counter = $this->listCounterType($meta);
            if ($counter !== null) {
                $attributes[] = 'data-counter="' . htmlspecialchars($counter, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '"';
            }
        }

        $attrHtml = $attributes === [] ? '' : ' ' . implode(' ', $attributes);

        return '<' . $tag . $attrHtml . '>' . $inner . '</' . $tag . '>';
    }

    /**
     * @param list<mixed>          $items
     * @param array<string, mixed> $meta
     */
    private function renderListItems(array $items, string $listStyle, array $meta): string
    {
        $html = '';

        foreach ($items as $item) {
            if (is_string($item)) {
                $html .= '<li>' . $this->sanitizeInline($item) . '</li>';

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $content  = $this->sanitizeInline((string) ($item['content'] ?? ''));
            $children = is_array($item['items'] ?? null) ? $item['items'] : [];
            $nested   = $children === [] ? '' : $this->renderList([
                'style' => $listStyle,
                'meta'  => [
                    'counterType' => $meta['counterType'] ?? null,
                ],
                'items' => $children,
            ]);

            $html .= '<li>' . $content . $nested . '</li>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function listStart(array $meta): ?int
    {
        if (! array_key_exists('start', $meta) || ! is_numeric($meta['start'])) {
            return null;
        }

        $start = (int) $meta['start'];
        if ($start === 1 || $start < -9999 || $start > 99999) {
            return null;
        }

        return $start;
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function listCounterType(array $meta): ?string
    {
        $type = is_string($meta['counterType'] ?? null) ? $meta['counterType'] : '';

        if ($type === '' || $type === 'numeric' || ! isset(self::COUNTER_TYPES[$type])) {
            return null;
        }

        return $type;
    }

    /**
     * Keep only allowed inline tags and safe http(s)/mailto links.
     *
     * DOMDocument is avoided here: LIBXML_HTML_NOIMPLIED drops inner tags and
     * the default HTML parser mangles UTF-8. After strip_tags the markup is
     * simple enough to clean with targeted replacements.
     */
    private function sanitizeInline(string $html): string
    {
        $html = strip_tags($html, '<' . implode('><', self::ALLOWED_TAGS) . '>');

        if ($html === '') {
            return '';
        }

        // Drop every attribute on emphasis tags; they never need attributes.
        $html = preg_replace('/<(b|i|strong|em|mark)(\s[^>]*)?>/i', '<$1>', $html) ?? $html;

        // Rebuild <a> so only a validated href and safe target/rel survive.
        $html = preg_replace_callback(
            '/<a\s+([^>]*+)>/i',
            function (array $match): string {
                if (preg_match('/href\s*=\s*([\'"])(.*?)\1/i', $match[1], $href) !== 1
                    || ! $this->isAllowedHref($href[2])
                ) {
                    return '<a>';
                }

                $attributes = [
                    'href="' . htmlspecialchars($href[2], ENT_QUOTES | ENT_HTML5, 'UTF-8') . '"',
                ];
                $target     = null;

                if (preg_match('/target\s*=\s*([\'"])(.*?)\1/i', $match[1], $found) === 1
                    && $this->isAllowedTarget($found[2])
                ) {
                    $target       = strtolower($found[2]);
                    $attributes[] = 'target="' . htmlspecialchars($target, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '"';
                }

                $rels = [];

                if (preg_match('/rel\s*=\s*([\'"])(.*?)\1/i', $match[1], $found) === 1) {
                    $rels = $this->allowedRels($found[2]);
                }

                if ($target === '_blank') {
                    $rels = array_values(array_unique([...$rels, 'noopener', 'noreferrer']));
                }

                if ($rels !== []) {
                    $attributes[] = 'rel="' . htmlspecialchars(implode(' ', $rels), ENT_QUOTES | ENT_HTML5, 'UTF-8') . '"';
                }

                return '<a ' . implode(' ', $attributes) . '>';
            },
            $html,
        ) ?? $html;

        return $html;
    }

    private function isAllowedHref(string $href): bool
    {
        if ($href === '') {
            return false;
        }

        $lower = strtolower($href);
        if (str_contains($lower, 'javascript:') || str_contains($lower, 'data:')) {
            return false;
        }

        return (bool) preg_match('#^(https?://|mailto:)#i', $href);
    }

    private function isAllowedTarget(string $target): bool
    {
        return in_array(strtolower($target), self::ALLOWED_TARGETS, true);
    }

    /**
     * @return list<string>
     */
    private function allowedRels(string $rel): array
    {
        $tokens = preg_split('/\s+/', strtolower(trim($rel))) ?: [];
        $safe   = [];

        foreach ($tokens as $token) {
            if (in_array($token, self::ALLOWED_RELS, true) && ! in_array($token, $safe, true)) {
                $safe[] = $token;
            }
        }

        return $safe;
    }
}
