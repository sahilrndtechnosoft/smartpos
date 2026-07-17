<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Inventories\Pages\CreateInventory;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Filament\Resources\PurchaseReturns\Pages\CreatePurchaseReturn;
use App\Filament\Resources\PurchaseReturns\Schemas\PurchaseReturnForm;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseReturnCreateTest extends TestCase
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

    public function test_purchase_return_decrements_stock_and_records_a_refund(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $supplier = Supplier::query()->firstOrFail();
        $stockAtStart = $product->qty;
        $item = InventoryForm::buildLineItemFromProduct($product, 10);

        $inventoryTest = Livewire::test(CreateInventory::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'date' => now()->toDateTimeString(),
                'status' => 'pending',
                'tax_inclusive' => false,
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $inventory = $inventoryTest->instance()->record;

        $this->assertSame($stockAtStart + 10, $product->refresh()->qty);

        $returnableItems = PurchaseReturnForm::returnableItemsFor($inventory->id);
        $this->assertCount(1, $returnableItems);

        $returnableItems[0]['qty'] = 4;

        $returnTest = Livewire::test(CreatePurchaseReturn::class)
            ->fillForm([
                'inventory_id' => $inventory->id,
                'supplier_id' => $inventory->supplier_id,
                'returned_at' => now()->toDateTimeString(),
                'refund_mode' => 'cod',
                'items' => $returnableItems,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $purchaseReturn = $returnTest->instance()->record;

        $this->assertSame($stockAtStart + 10 - 4, $product->refresh()->qty);
        $this->assertSame(4 * (float) $item['purchase_rate'], (float) $purchaseReturn->refresh()->total);
        $this->assertSame(1, $purchaseReturn->payments()->count());
        $this->assertSame(6, PurchaseReturnForm::remainingQtyForInventoryItem($returnableItems[0]['inventory_item_id']));
    }

    public function test_purchase_return_cannot_exceed_the_purchased_qty(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $supplier = Supplier::query()->firstOrFail();
        $item = InventoryForm::buildLineItemFromProduct($product, 3);

        $inventoryTest = Livewire::test(CreateInventory::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'date' => now()->toDateTimeString(),
                'status' => 'pending',
                'tax_inclusive' => false,
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $inventory = $inventoryTest->instance()->record;
        $returnableItems = PurchaseReturnForm::returnableItemsFor($inventory->id);
        $returnableItems[0]['qty'] = 10;

        Livewire::test(CreatePurchaseReturn::class)
            ->fillForm([
                'inventory_id' => $inventory->id,
                'supplier_id' => $inventory->supplier_id,
                'returned_at' => now()->toDateTimeString(),
                'refund_mode' => 'cod',
                'items' => $returnableItems,
            ])
            ->call('create')
            ->assertHasFormErrors();
    }
}
