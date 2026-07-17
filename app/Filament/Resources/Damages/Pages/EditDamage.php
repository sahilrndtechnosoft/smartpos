<?php

namespace App\Filament\Resources\Damages\Pages;

use App\Filament\Resources\Concerns\AlignsFormActionsStart;
use App\Filament\Resources\Damages\DamageResource;
use App\Support\StockAdjuster;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDamage extends EditRecord
{
    use AlignsFormActionsStart;

    protected static string $resource = DamageResource::class;

    protected ?string $productIdBeforeSave = null;

    protected int $qtyBeforeSave = 0;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->productIdBeforeSave = $this->record->product_id;
        $this->qtyBeforeSave = $this->record->qty;

        return $data;
    }

    protected function afterSave(): void
    {
        // Undo the original write-off, then apply the new one.
        StockAdjuster::apply([$this->productIdBeforeSave => $this->qtyBeforeSave]);
        StockAdjuster::apply([$this->record->product_id => -$this->record->qty]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
                ->tooltip('Actions'),
        ];
    }
}
