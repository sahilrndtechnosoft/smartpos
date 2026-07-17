<?php

namespace App\Filament\Resources\Damages\Pages;

use App\Filament\Resources\Damages\DamageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDamages extends ListRecords
{
    protected static string $resource = DamageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Record damage')
                ->icon(Heroicon::Plus),
        ];
    }
}
