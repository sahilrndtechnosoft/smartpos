@auth
    @php
        $shortcuts = app(\App\Services\KeyboardShortcutResolver::class)->activeShortcutsForUser();
        $shortcutsPayload = [
            'shortcuts' => array_values($shortcuts),
        ];
    @endphp

    <script>
        window.__smartposKeyboardShortcuts = @json($shortcutsPayload);
    </script>
    <script src="{{ asset('js/filament-keyboard-shortcuts.js') }}" defer></script>
@endauth
