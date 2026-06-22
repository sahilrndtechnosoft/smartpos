<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Actions\PrintDocumentAction;
use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PrintDocumentAction::make('print', 'Print invoice', 'print.orders.invoice'),
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
                ->tooltip('Actions'),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotals();
        $this->record->refresh();
    }
}
