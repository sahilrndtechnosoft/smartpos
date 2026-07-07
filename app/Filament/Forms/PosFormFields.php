<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\TextInput;

class PosFormFields
{
    public static function barcodeScan(): TextInput
    {
        return TextInput::make('barcodeScan')
            ->label('Scan barcode / SKU')
            ->placeholder('Scan or type barcode / SKU, then press Enter')
            ->dehydrated(false)
            ->autocomplete('off')
            ->extraInputAttributes([
                'data-pos-barcode-input' => 'true',
            ])
            ->helperText('F4 refocuses scanner. Scanning the same product again increases quantity.')
            ->columnSpanFull();
    }
}
