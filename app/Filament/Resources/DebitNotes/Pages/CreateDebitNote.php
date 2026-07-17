<?php

namespace App\Filament\Resources\DebitNotes\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\DebitNotes\DebitNoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateDebitNote extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = DebitNoteResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'DN-'.Str::upper(Str::random(8)),
            'note_date' => now(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'DN-'.Str::upper(Str::random(8));
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
