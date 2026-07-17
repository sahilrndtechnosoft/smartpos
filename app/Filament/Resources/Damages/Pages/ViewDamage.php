<?php

namespace App\Filament\Resources\Damages\Pages;

use App\Filament\Resources\Damages\DamageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDamage extends ViewRecord
{
    protected static string $resource = DamageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            EditAction::make(),
        ];
    }
}
