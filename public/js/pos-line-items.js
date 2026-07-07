(() => {
    const BARCODE_SELECTOR = '[data-pos-barcode-input]';

    const focusBarcodeInput = () => {
        const input = document.querySelector(BARCODE_SELECTOR);

        if (! input) {
            return;
        }

        window.requestAnimationFrame(() => {
            input.focus();
            input.select?.();
        });
    };

    const bindBarcodeInputs = () => {
        document.querySelectorAll(BARCODE_SELECTOR).forEach((input) => {
            if (input.dataset.posBound === 'true') {
                return;
            }

            input.dataset.posBound = 'true';

            input.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                const component = window.Livewire?.find(
                    input.closest('[wire\\:id]')?.getAttribute('wire:id'),
                );

                component?.call('scanBarcode', input.value);
            });
        });
    };

    document.addEventListener('livewire:init', () => {
        window.Livewire.on('pos-barcode-focus', focusBarcodeInput);

        window.Livewire.hook('morph.updated', () => {
            bindBarcodeInputs();
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'F4') {
            return;
        }

        const input = document.querySelector(BARCODE_SELECTOR);

        if (! input) {
            return;
        }

        event.preventDefault();
        focusBarcodeInput();
    });

    document.addEventListener('DOMContentLoaded', () => {
        bindBarcodeInputs();

        if (document.querySelector(BARCODE_SELECTOR)) {
            focusBarcodeInput();
        }
    });
})();
