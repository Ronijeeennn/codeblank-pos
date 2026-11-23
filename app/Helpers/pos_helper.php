<?php

/**
 * View/formatting helpers shared by every page (autoloaded as "pos" in BaseController).
 */

if (! function_exists('money')) {
    function money($amount): string
    {
        return '₱' . number_format((float) $amount, 2);
    }
}

if (! function_exists('fmt_date')) {
    function fmt_date(?string $datetime): string
    {
        return $datetime ? date('M j, Y g:i A', strtotime($datetime)) : '';
    }
}

if (! function_exists('old_value')) {
    /** Re-fill an input: failed submission first, then the saved record, then blank. Always escaped. */
    function old_value(string $name, $default = ''): string
    {
        $value = old($name, null, false);

        return esc((string) ($value ?? $default ?? ''), 'attr');
    }
}

if (! function_exists('upload_url')) {
    function upload_url(string $folder, ?string $file): ?string
    {
        return $file ? base_url('uploads/' . $folder . '/' . rawurlencode($file)) : null;
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    }
}

if (! function_exists('tint')) {
    /** A soft background/text colour pair derived from a name, so placeholders look distinct but stay consistent. */
    function tint(string $seed): string
    {
        $hue = crc32(strtolower($seed)) % 360;

        return "background:hsl($hue 70% 90%);color:hsl($hue 55% 27%)";
    }
}

if (! function_exists('icon')) {
    /** Inline SVG icon (stroke follows the text colour). Static markup, safe to echo. */
    function icon(string $name, int $size = 18): string
    {
        static $paths = [
            'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
            'receipt'   => '<path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/><path d="M9 7h6M9 11h6M9 15h4"/>',
            'history'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'box'       => '<path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
            'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
            'badge'     => '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2"/><path d="M14 10h4M14 14h4"/>',
            'plus'      => '<path d="M12 5v14M5 12h14"/>',
            'minus'     => '<path d="M5 12h14"/>',
            'search'    => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
            'edit'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            'trash'     => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/>',
            'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
            'check'     => '<path d="M20 6L9 17l-5-5"/>',
            'alert'     => '<path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
            'trend'     => '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>',
            'image'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
            'store'     => '<path d="M3 9l1.5-5h15L21 9"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M5 12v9h14v-9"/><path d="M10 21v-5h4v5"/>',
        ];

        return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            . 'stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? '') . '</svg>';
    }
}
