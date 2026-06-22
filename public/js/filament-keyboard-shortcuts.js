(() => {
    'use strict';

    const config = window.__smartposKeyboardShortcuts ?? { shortcuts: [] };

    function isEditableTarget(target) {
        if (!target || !(target instanceof Element)) {
            return false;
        }

        const editable = target.closest('input, textarea, select, [contenteditable=""], [contenteditable="true"], [contenteditable="plaintext-only"]');

        if (!editable) {
            return false;
        }

        if (editable instanceof HTMLInputElement) {
            const type = (editable.type || 'text').toLowerCase();

            if (['button', 'submit', 'reset', 'checkbox', 'radio', 'file', 'hidden', 'range', 'color'].includes(type)) {
                return false;
            }
        }

        return true;
    }

    function normalizeKey(key) {
        if (!key) {
            return '';
        }

        const lower = key.toLowerCase();

        if (lower === ' ') {
            return 'space';
        }

        if (lower === 'esc') {
            return 'escape';
        }

        return lower;
    }

    function eventCombination(event) {
        const parts = [];

        if (event.ctrlKey || event.metaKey) {
            parts.push('ctrl');
        }

        if (event.shiftKey) {
            parts.push('shift');
        }

        if (event.altKey) {
            parts.push('alt');
        }

        const key = normalizeKey(event.key);

        if (!['control', 'shift', 'alt', 'meta'].includes(key)) {
            parts.push(key);
        }

        return parts.join('+');
    }

    function findShortcut(combination) {
        return config.shortcuts.find((shortcut) => shortcut.combination === combination);
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    }

    async function runDeleteShortcut(shortcut) {
        const message = shortcut.confirm_message || `Run "${shortcut.name}"?`;

        if (!window.confirm(message)) {
            return;
        }

        const response = await fetch(shortcut.run_url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            window.alert('Unable to complete the shortcut action.');
            return;
        }

        window.location.reload();
    }

    document.addEventListener('keydown', (event) => {
        if (isEditableTarget(event.target)) {
            return;
        }

        const combination = eventCombination(event);
        const shortcut = findShortcut(combination);

        if (!shortcut) {
            return;
        }

        if (shortcut.behavior === 'delete') {
            if (!shortcut.run_url) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            runDeleteShortcut(shortcut);

            return;
        }

        if (!shortcut.url) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        window.location.assign(shortcut.url);
    }, true);
})();
