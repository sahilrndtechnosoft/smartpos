<?php

namespace App\Filament\Resources\ContraEntries\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\ContraEntries\ContraEntryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateContraEntry extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = ContraEntryResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'CTR-'.Str::upper(Str::random(8)),
            'entry_date' => now(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'CTR-'.Str::upper(Str::random(8));
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->postLedgerEntries();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
