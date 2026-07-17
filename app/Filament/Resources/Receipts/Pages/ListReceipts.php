<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Filament\Resources\Receipts\ReceiptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New receipt')
                ->icon(Heroicon::Plus),
        ];
    }
}
