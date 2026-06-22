<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Actions\PrintDocumentAction;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PrintDocumentAction::make('print', 'Print invoice', 'print.orders.invoice'),
            DeleteAction::make(),
            EditAction::make(),
        ];
    }
}
