<?php

/**
 * Czech date/time formatting and parsing used in views and models.
 *
 * The admin datepicker shows and submits d. m. Y H:i. ISO values and
 * date-only input are still accepted; date-only becomes midnight.
 */
if (! function_exists('parse_app_datetime')) {
    /**
     * Parse an application datetime string into a DateTimeImmutable.
     */
    function parse_app_datetime(?string $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $value = trim(str_replace('T', ' ', $value));
        if ($value === '') {
            return null;
        }

        $formats = [
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/' => 'Y-m-d H:i:s',
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/'       => 'Y-m-d H:i',
            '/^\d{4}-\d{2}-\d{2}$/'                   => 'Y-m-d',
        ];

        foreach ($formats as $pattern => $format) {
            if (preg_match($pattern, $value) !== 1) {
                continue;
            }

            $parsed = DateTimeImmutable::createFromFormat('!' . $format, $value);
            if ($parsed instanceof DateTimeImmutable) {
                return $parsed;
            }
        }

        if (preg_match('/^(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/', $value, $match) === 1) {
            $normalized = sprintf(
                '%04d-%02d-%02d %02d:%02d:%02d',
                (int) $match[3],
                (int) $match[2],
                (int) $match[1],
                (int) ($match[4] ?? 0),
                (int) ($match[5] ?? 0),
                (int) ($match[6] ?? 0),
            );

            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $normalized);
            if ($parsed instanceof DateTimeImmutable) {
                return $parsed;
            }
        }

        return null;
    }
}

if (! function_exists('normalize_app_datetime')) {
    /**
     * Normalize a submitted value to Y-m-d H:i:s, or null when empty.
     */
    function normalize_app_datetime(?string $value): ?string
    {
        return parse_app_datetime($value)?->format('Y-m-d H:i:s');
    }
}

if (! function_exists('format_czech_date')) {
    /**
     * Convert a stored datetime to d. m. Y, optionally with H:i.
     */
    function format_czech_date(?string $date, bool $withTime = true): string
    {
        $parsed = parse_app_datetime($date);

        if (! $parsed instanceof DateTimeImmutable) {
            return (string) $date;
        }

        return $withTime
            ? $parsed->format('d. m. Y H:i')
            : $parsed->format('d. m. Y');
    }
}

if (! function_exists('format_czech_time')) {
    /**
     * Convert a stored datetime to H:i.
     */
    function format_czech_time(?string $date): string
    {
        $parsed = parse_app_datetime($date);

        return $parsed instanceof DateTimeImmutable
            ? $parsed->format('H:i')
            : '';
    }
}

if (! function_exists('format_datetime_input')) {
    /**
     * Value for the calendar input (d. m. Y H:i).
     */
    function format_datetime_input(?string $date): string
    {
        return format_czech_date($date === null || $date === '' ? null : $date);
    }
}
