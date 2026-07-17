<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\CreditNotes\CreditNoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCreditNote extends CreateRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = CreditNoteResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'code' => 'CN-'.Str::upper(Str::random(8)),
            'note_date' => now(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'CN-'.Str::upper(Str::random(8));
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
