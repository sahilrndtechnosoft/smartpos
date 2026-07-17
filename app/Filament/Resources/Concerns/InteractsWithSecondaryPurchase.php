<?php

namespace App\Filament\Resources\Concerns;

use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

trait InteractsWithSecondaryPurchase
{
    /**
     * A modal, triggered by the F8 shortcut during purchase entry, that appends a
     * secondary-flagged item to the same purchase bill without leaving the screen.
     */
    protected function secondaryPurchaseAction(): Action
    {
        return Action::make('secondaryPurchase')
            ->label('Secondary purchase')
            ->color('warning')
            ->modalHeading('Add secondary purchase item')
            ->modalSubmitActionLabel('Add item')
            ->schema([
                Select::make('product_id')
                    ->label('Product')
                    ->options(fn (): array => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                TextInput::make('qty')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),

                TextInput::make('purchase_rate')
                    ->label('Cost price')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $product = Product::query()->find($data['product_id']);

                if (! $product) {
                    return;
                }

                $item = InventoryForm::buildLineItemFromProduct($product, (int) $data['qty']);
                $item['purchase_rate'] = $data['purchase_rate'];
                $item['is_secondary'] = true;

                $this->data['items'][] = $item;

                Notification::make()
                    ->title('Added to secondary purchase')
                    ->body("{$product->name} added.")
                    ->success()
                    ->send();
            });
    }
}
