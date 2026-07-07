<?php

namespace App\Filament\Resources\Inventories\Schemas;

use App\Filament\Forms\PosFormFields;
use App\Models\Inventory;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema, bool $includeSupplier = true): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Details')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                ...($includeSupplier ? [
                                    Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->relationship('supplier', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false),
                                ] : []),

                                DateTimePicker::make('date')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(true)
                                    ->displayFormat('M j, Y H:i:s'),

                                ToggleButtons::make('status')
                                    ->label('Payment Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->inline()
                                    ->grouped(),
                            ]),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: Inventory::class, ignoreRecord: true)
                            ->hidden()
                            ->dehydrated(),
                    ]),

                Section::make('Items Details')
                    ->schema([
                        PosFormFields::barcodeScan(),

                        Repeater::make('items')
                            ->relationship()
                            ->label('Items')
                            ->schema(self::itemFields())
                            ->addActionLabel('Add to items')
                            ->collapsible()
                            ->cloneable()
                            ->orderColumn('sr')
                            ->defaultItems(0)
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::normalizeItemData($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::normalizeItemData($data))
                            ->columnSpanFull(),
                    ]),

                Section::make('Other Details')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('file')
                            ->label('File')
                            ->directory('inventories')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
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
                ->columns(6)
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            if (blank($state)) {
                                return;
                            }

                            $product = Product::query()->find($state);

                            if (! $product) {
                                return;
                            }

                            $set('purchase_rate', $product->purchase_rate);
                            $set('rate_a', $product->rate_a);
                            $set('mrp', $product->mrp);
                        }),

                    TextInput::make('qty')
                        ->label('Available Stock')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required(),

                    TextInput::make('purchase_rate')
                        ->label('Cost price')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('rate_a')
                        ->label('Sale Price')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('mrp')
                        ->label('Old Price')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    DatePicker::make('expiry_date')
                        ->label('Expiry date')
                        ->native(false),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeItemData(array $data): array
    {
        $salePrice = $data['rate_a'] ?? 0;

        $data['rate_b'] = $data['rate_b'] ?? $salePrice;
        $data['rate_c'] = $data['rate_c'] ?? $salePrice;
        $data['is_locked'] = $data['is_locked'] ?? false;

        return $data;
    }

    public static function buildLineItemFromProduct(Product $product, int $qty = 1): array
    {
        return self::normalizeItemData([
            'product_id' => $product->id,
            'qty' => $qty,
            'purchase_rate' => $product->purchase_rate,
            'rate_a' => $product->rate_a,
            'mrp' => $product->mrp,
        ]);
    }
}
