<?php

namespace App\Filament\Imports;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Services\ProductLookupService;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InventoryItemImporter extends Importer
{
    protected static ?string $model = InventoryItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('product')
                ->label('SKU / Barcode')
                ->example('AMUL-MILK-1L')
                ->exampleHeader('sku')
                ->requiredMapping()
                ->guess(['sku', 'barcode', 'product', 'product_sku'])
                ->rules(['required', 'string', 'max:255'])
                ->fillRecordUsing(fn (): null => null),

            ImportColumn::make('qty')
                ->label('Quantity')
                ->example('10')
                ->requiredMapping()
                ->guess(['quantity', 'available stock', 'stock'])
                ->integer()
                ->rules(['required', 'integer', 'min:1']),

            ImportColumn::make('purchase_rate')
                ->label('Cost price')
                ->example('25')
                ->numeric(decimalPlaces: 2)
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('rate_a')
                ->label('Sale price')
                ->example('30')
                ->numeric(decimalPlaces: 2)
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('mrp')
                ->label('MRP')
                ->example('35')
                ->numeric(decimalPlaces: 2)
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('expiry_date')
                ->label('Expiry date')
                ->example('2026-12-31')
                ->rules(['nullable', 'date']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('supplier_id')
                ->label('Supplier')
                ->options(fn (): array => Supplier::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->required()
                ->native(false),

            DateTimePicker::make('date')
                ->label('Receipt date')
                ->default(now())
                ->required()
                ->native(false),

            ToggleButtons::make('status')
                ->label('Payment status')
                ->options([
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ])
                ->default('pending')
                ->required()
                ->inline()
                ->grouped(),
        ];
    }

    public function resolveRecord(): ?InventoryItem
    {
        return new InventoryItem;
    }

    protected function beforeFill(): void
    {
        $productCode = trim((string) ($this->data['product'] ?? ''));

        $product = app(ProductLookupService::class)->findByBarcodeOrSku($productCode);

        if (! $product) {
            throw new RowImportFailedException("No active product found for SKU or barcode [{$productCode}].");
        }

        $this->data['product_id'] = $product->id;

        foreach (['purchase_rate', 'rate_a', 'mrp'] as $field) {
            if (blank($this->data[$field] ?? null)) {
                $this->data[$field] = $product->{$field};
            }
        }

        if (blank($this->data['rate_b'] ?? null)) {
            $this->data['rate_b'] = $this->data['rate_a'];
        }

        if (blank($this->data['rate_c'] ?? null)) {
            $this->data['rate_c'] = $this->data['rate_a'];
        }
    }

    protected function beforeCreate(): void
    {
        $inventory = $this->resolveInventoryForImport();

        $this->record->inventory_id = $inventory->id;
        $this->record->product_id = $this->data['product_id'];
        $this->record->sr = (int) $inventory->items()->max('sr') + 1;
        $this->record->is_locked = false;
    }

    protected function resolveInventoryForImport(): Inventory
    {
        $importId = $this->import->getKey();
        $cacheKey = "inventory_import:{$importId}";

        return Cache::lock("{$cacheKey}:lock", 30)->block(10, function () use ($cacheKey): Inventory {
            $inventoryId = Cache::get($cacheKey);

            if ($inventoryId) {
                return Inventory::query()->findOrFail($inventoryId);
            }

            $inventory = Inventory::query()->create([
                'code' => 'INV-'.Str::upper(Str::random(8)),
                'supplier_id' => $this->options['supplier_id'],
                'date' => $this->options['date'] ?? now(),
                'status' => $this->options['status'] ?? 'pending',
            ]);

            Cache::put($cacheKey, $inventory->id, now()->addDay());

            return $inventory;
        });
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Imported '.number_format($import->successful_rows).' inventory line item(s).';

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' row(s) failed.';
        }

        return $body;
    }
}
