<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Inventories\Pages\CreateInventory;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryCreateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@smartpos.local')->firstOrFail();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($admin);
        Filament::auth()->login($admin);
    }

    public function test_exclusive_tax_is_added_on_top_of_the_cost_price(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $taxRate = $product->taxGroup?->effectiveRate() ?? 0.0;

        $this->assertGreaterThan(0.0, $taxRate);

        $tax = InventoryForm::computeTax(qty: 10, rate: 50, taxRatePercent: $taxRate, inclusive: false);

        $this->assertSame(round(10 * 50 * $taxRate / 100, 2), $tax);
    }

    public function test_inclusive_tax_is_backed_out_of_the_cost_price(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $taxRate = $product->taxGroup?->effectiveRate() ?? 0.0;

        $this->assertGreaterThan(0.0, $taxRate);

        $gross = 10 * 50;
        $tax = InventoryForm::computeTax(qty: 10, rate: 50, taxRatePercent: $taxRate, inclusive: true);

        $this->assertSame(round($gross - ($gross / (1 + $taxRate / 100)), 2), $tax);
        $this->assertLessThan(round(10 * 50 * $taxRate / 100, 2), $tax);
    }

    public function test_secondary_purchase_action_appends_a_flagged_item(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();

        $test = Livewire::test(CreateInventory::class)
            ->callAction('secondaryPurchase', data: [
                'product_id' => $product->id,
                'qty' => 5,
                'purchase_rate' => 40,
            ]);

        $items = $test->instance()->data['items'] ?? [];

        $this->assertCount(1, $items);
        $this->assertTrue($items[0]['is_secondary']);
        $this->assertSame(5, $items[0]['qty']);
        $this->assertSame(40.0, (float) $items[0]['purchase_rate']);
    }

    public function test_inventory_create_persists_computed_tax_total(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $supplier = Supplier::query()->firstOrFail();
        $item = InventoryForm::buildLineItemFromProduct($product, 10);
        $item['purchase_rate'] = 50;

        $test = Livewire::test(CreateInventory::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'date' => now()->toDateTimeString(),
                'status' => 'pending',
                'tax_inclusive' => false,
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $inventoryItem = $test->instance()->record->items()->first();
        $taxRate = $product->taxGroup?->effectiveRate() ?? 0.0;

        $this->assertSame(round(10 * 50 * $taxRate / 100, 2), (float) $inventoryItem->tax_total);
    }

    public function test_purchase_increments_product_stock(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $supplier = Supplier::query()->firstOrFail();
        $stockBefore = $product->qty;
        $item = InventoryForm::buildLineItemFromProduct($product, 7);

        Livewire::test(CreateInventory::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'date' => now()->toDateTimeString(),
                'status' => 'pending',
                'tax_inclusive' => false,
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($stockBefore + 7, $product->refresh()->qty);
    }
}
