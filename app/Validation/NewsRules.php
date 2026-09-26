<?php

namespace App\Validation;

/**
 * Custom validation rules for news forms.
 */
class NewsRules
{
    /**
     * Content must be Editor.js JSON with at least one non-empty allowed block.
     *
     * @param mixed $value
     */
    public function valid_editorjs($value, ?string &$error = null): bool
    {
        if (! is_string($value) || trim($value) === '') {
            $error = 'Obsah aktuality je povinný.';

            return false;
        }

        $document = json_decode($value, true);
        if (! is_array($document) || ! isset($document['blocks']) || ! is_array($document['blocks'])) {
            $error = 'Obsah aktuality není ve správném formátu.';

            return false;
        }

        foreach ($document['blocks'] as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            if ($type === 'paragraph') {
                $text = trim(html_entity_decode(strip_tags((string) ($data['text'] ?? ''))));
                if ($text !== '') {
                    return true;
                }
            }

            if ($type === 'list' && $this->listHasText($data['items'] ?? [])) {
                return true;
            }
        }

        $error = 'Obsah aktuality je povinný.';

        return false;
    }

    /**
     * Accepts Y-m-d, Y-m-d H:i or Y-m-d H:i:s (and the Czech picker display).
     *
     * @param mixed $value
     */
    public function valid_datetime($value, ?string &$error = null): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (! is_string($value) || parse_app_datetime($value) === null) {
            $error = 'Zadejte platné datum a čas.';

            return false;
        }

        return true;
    }

    /**
     * Optional visible_to must be on or after visible_from (including time).
     *
     * @param mixed                $value
     * @param array<string, mixed> $data
     */
    public function date_on_or_after($value, string $params, array $data, ?string &$error = null): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $other = $data[$params] ?? null;
        if (! is_string($other) || $other === '') {
            return true;
        }

        $end   = parse_app_datetime((string) $value);
        $start = parse_app_datetime($other);

        if ($end === null || $start === null || $end < $start) {
            $error = 'Konec zobrazení nesmí být dřívější než začátek.';

            return false;
        }

        return true;
    }

    /**
     * @param mixed $items
     */
    private function listHasText($items): bool
    {
        if (! is_array($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (is_string($item) && trim(strip_tags($item)) !== '') {
                return true;
            }

            if (is_array($item)) {
                $content = trim(html_entity_decode(strip_tags((string) ($item['content'] ?? ''))));
                if ($content !== '') {
                    return true;
                }

                if ($this->listHasText($item['items'] ?? [])) {
                    return true;
                }
            }
        }

        return false;
    }
}
