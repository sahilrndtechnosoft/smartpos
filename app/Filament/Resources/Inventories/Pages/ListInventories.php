<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Imports\InventoryItemImporter;
use App\Filament\Resources\Inventories\InventoryResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Import')
                ->icon(Heroicon::ArrowUpTray)
                ->importer(InventoryItemImporter::class)
                ->visible(fn (): bool => InventoryResource::canCreate()),
            CreateAction::make()
                ->label('New inventory')
                ->icon(Heroicon::Plus),
        ];
    }
}
