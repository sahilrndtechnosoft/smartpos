<?php

namespace App\Support;

class KeyboardShortcutCombination
{
    /**
     * @var list<string>
     */
    protected const MODIFIERS = ['ctrl', 'shift', 'alt', 'meta'];

    /**
     * @var list<string>
     */
    protected const ALLOWED_KEYS = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'f7', 'f8', 'f9', 'f10', 'f11', 'f12',
        'escape', 'enter', 'space', 'tab', 'backspace', 'delete', 'arrowup', 'arrowdown', 'arrowleft', 'arrowright',
        '/', '?', ',', '.', '-', '=', '[', ']', ';', '\'', '\\',
    ];

    public static function normalize(?string $combination): string
    {
        if (blank($combination)) {
            return '';
        }

        $parts = collect(explode('+', str_replace([' ', '_'], ['', '-'], strtolower(trim($combination)))))
            ->map(fn (string $part): string => match ($part) {
                'control', 'cmd', 'command', 'meta', '⌘' => 'ctrl',
                'option' => 'alt',
                'esc' => 'escape',
                'return' => 'enter',
                default => $part,
            })
            ->filter()
            ->unique()
            ->values();

        $modifiers = $parts->filter(fn (string $part): bool => in_array($part, self::MODIFIERS, true))->sort()->values();
        $keys = $parts->reject(fn (string $part): bool => in_array($part, self::MODIFIERS, true))->values();

        return $modifiers->merge($keys)->implode('+');
    }

    public static function isValid(?string $combination): bool
    {
        $normalized = self::normalize($combination);

        if ($normalized === '') {
            return false;
        }

        $parts = explode('+', $normalized);
        $keys = array_filter($parts, fn (string $part): bool => ! in_array($part, self::MODIFIERS, true));

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (! in_array($key, self::ALLOWED_KEYS, true)) {
                return false;
            }
        }

        return true;
    }

    public static function display(?string $combination): string
    {
        return strtoupper(str_replace('+', ' + ', self::normalize($combination)));
    }
}
