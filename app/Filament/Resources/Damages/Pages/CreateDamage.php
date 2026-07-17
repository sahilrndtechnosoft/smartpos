<?php

namespace App\Filament\Resources\Damages\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Damages\DamageResource;
use App\Support\StockAdjuster;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateDamage extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = DamageResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'DMG-'.Str::upper(Str::random(8)),
            'damaged_at' => now(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'DMG-'.Str::upper(Str::random(8));
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        StockAdjuster::apply([
            $this->record->product_id => -$this->record->qty,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
