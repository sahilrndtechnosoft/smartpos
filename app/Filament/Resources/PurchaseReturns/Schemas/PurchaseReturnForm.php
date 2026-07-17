<?php

namespace App\Filament\Resources\PurchaseReturns\Schemas;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PurchaseReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Return details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('inventory_id')
                                    ->label('Purchase bill')
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search): array => Inventory::query()
                                        ->where('code', 'like', "%{$search}%")
                                        ->orWhereHas('supplier', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn (Inventory $inventory): array => [$inventory->id => self::inventoryOptionLabel($inventory)])
                                        ->all())
                                    ->getOptionLabelUsing(fn ($value): ?string => filled($value) && ($inventory = Inventory::query()->find($value))
                                        ? self::inventoryOptionLabel($inventory)
                                        : null)
                                    ->required()
                                    ->live()
                                    ->native(false)
                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                        $inventory = filled($state) ? Inventory::query()->find($state) : null;

                                        $set('supplier_id', $inventory?->supplier_id);
                                        $set('items', self::returnableItemsFor($state));
                                        $set('total', 0);
                                    }),

                                DateTimePicker::make('returned_at')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),

                                Select::make('refund_mode')
                                    ->label('Refund via')
                                    ->options([
                                        'cod' => 'Cash',
                                        'online' => 'Online',
                                        'card' => 'Card',
                                        'upi' => 'UPI',
                                        'wallet' => 'Wallet',
                                        'credit' => 'Adjust against dues',
                                    ])
                                    ->default('cod')
                                    ->required()
                                    ->native(false),
                            ]),

                        Textarea::make('reason')
                            ->rows(2)
                            ->columnSpanFull(),

                        Hidden::make('supplier_id')
                            ->required(),

                        Hidden::make('current_return_id')
                            ->dehydrated(false),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(32)
                            ->unique(table: PurchaseReturn::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Section::make('Items to return')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->schema(self::itemFields())
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                $set('total', round((float) collect($get('items') ?? [])->sum('amount'), 2));
                            })
                            ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals')
                    ->schema([
                        TextInput::make('total')
                            ->label('Refund total')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function itemFields(): array
    {
        return [
            Grid::make()
                ->columns(4)
                ->schema([
                    Hidden::make('inventory_item_id')
                        ->required(),

                    Hidden::make('product_id')
                        ->required(),

                    TextInput::make('product_name')
                        ->label('Product')
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('purchase_rate')
                        ->label('Cost price')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('qty')
                        ->label('Return qty')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(fn (Get $get): int => self::remainingQtyForInventoryItem(
                            $get('inventory_item_id'),
                            $get('../../current_return_id'),
                        ))
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            $qty = (float) ($get('qty') ?? 0);
                            $rate = (float) ($get('purchase_rate') ?? 0);
                            $set('amount', round($qty * $rate, 2));
                        }),

                    TextInput::make('amount')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                ]),
        ];
    }

    protected static function inventoryOptionLabel(Inventory $inventory): string
    {
        return "{$inventory->code} — ".($inventory->supplier?->name ?: '—');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function returnableItemsFor(?string $inventoryId): array
    {
        if (blank($inventoryId)) {
            return [];
        }

        $inventory = Inventory::query()->with('items')->find($inventoryId);

        if (! $inventory) {
            return [];
        }

        return $inventory->items
            ->map(function (InventoryItem $item): ?array {
                $remaining = self::remainingQtyForInventoryItem($item->id);

                if ($remaining <= 0) {
                    return null;
                }

                return [
                    'inventory_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? '',
                    'purchase_rate' => (float) $item->purchase_rate,
                    'qty' => 0,
                    'amount' => 0,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function remainingQtyForInventoryItem(?string $inventoryItemId, ?string $excludingPurchaseReturnId = null): int
    {
        if (blank($inventoryItemId)) {
            return 0;
        }

        $inventoryItem = InventoryItem::query()->find($inventoryItemId);

        if (! $inventoryItem) {
            return 0;
        }

        $returned = PurchaseReturnItem::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->when(filled($excludingPurchaseReturnId), fn ($query) => $query->where('purchase_return_id', '!=', $excludingPurchaseReturnId))
            ->sum('qty');

        return max(0, $inventoryItem->qty - (int) $returned);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function itemsFromPurchaseReturn(PurchaseReturn $purchaseReturn): array
    {
        return $purchaseReturn->items
            ->map(fn (PurchaseReturnItem $item): array => [
                'inventory_item_id' => $item->inventory_item_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'purchase_rate' => (float) $item->purchase_rate,
                'qty' => $item->qty,
                'amount' => (float) $item->amount,
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function persistItems(PurchaseReturn $purchaseReturn, array $items): void
    {
        $purchaseReturn->items()->delete();

        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $rate = (float) ($item['purchase_rate'] ?? 0);

            $purchaseReturn->items()->create([
                'inventory_item_id' => $item['inventory_item_id'],
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'] ?? '',
                'qty' => $qty,
                'purchase_rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ]);
        }
    }

    public static function recordRefundPayment(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->payments()->delete();

        if ((float) $purchaseReturn->total <= 0) {
            return;
        }

        $purchaseReturn->payments()->create([
            'code' => 'RFD-'.Str::upper(Str::random(10)),
            'type' => 'credit',
            'method' => $purchaseReturn->refund_mode,
            'amount' => $purchaseReturn->total,
            'status' => 'completed',
            'payment_at' => now(),
        ]);
    }
}
