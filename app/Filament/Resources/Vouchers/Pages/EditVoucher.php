<?php

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Vouchers\VoucherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVoucher extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->postLedgerEntries();
    }
}
