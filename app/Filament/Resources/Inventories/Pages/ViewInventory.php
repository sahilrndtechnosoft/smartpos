<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Actions\PrintDocumentAction;
use App\Filament\Resources\Inventories\InventoryResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PrintDocumentAction::make('print', 'Print invoice', 'print.inventories.invoice'),
            EditAction::make(),
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
                ->tooltip('More actions'),
        ];
    }
}
