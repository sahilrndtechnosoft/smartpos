<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\TextInput;

class PosFormFields
{
    public static function barcodeScan(bool $withSecondaryPurchase = false): TextInput
    {
        return TextInput::make('barcodeScan')
            ->label('Scan barcode / SKU')
            ->placeholder('Scan or type barcode / SKU, then press Enter')
            ->dehydrated(false)
            ->autocomplete('off')
            ->extraInputAttributes(array_filter([
                'data-pos-barcode-input' => 'true',
                'data-secondary-purchase-action' => $withSecondaryPurchase ? 'true' : null,
            ]))
            ->helperText($withSecondaryPurchase
                ? 'F4 refocuses scanner. Scanning the same product again increases quantity. F9 saves the bill. F8 opens secondary purchase.'
                : 'F4 refocuses scanner. Scanning the same product again increases quantity. F9 saves the bill.')
            ->columnSpanFull();
    }
}
