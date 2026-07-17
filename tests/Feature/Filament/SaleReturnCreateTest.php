<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\SaleReturns\Pages\CreateSaleReturn;
use App\Filament\Resources\SaleReturns\Schemas\SaleReturnForm;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class SaleReturnCreateTest extends TestCase
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

    public function test_sale_return_increments_stock_and_records_a_refund(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $stockAtStart = $product->qty;
        $item = OrderForm::buildLineItemFromProduct($product, 5);

        $orderTest = Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => Customer::cashCustomer()->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_mode' => 'cod',
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = $orderTest->instance()->record;

        $this->assertSame($stockAtStart - 5, $product->refresh()->qty);

        $returnableItems = SaleReturnForm::returnableItemsFor($order->id);
        $this->assertCount(1, $returnableItems);
        $this->assertSame(5, SaleReturnForm::remainingQtyForOrderItem($returnableItems[0]['order_item_id']));

        $returnableItems[0]['qty'] = 2;

        $returnTest = Livewire::test(CreateSaleReturn::class)
            ->fillForm([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'returned_at' => now()->toDateTimeString(),
                'refund_mode' => 'cod',
                'items' => $returnableItems,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $saleReturn = $returnTest->instance()->record;

        $this->assertSame($stockAtStart - 5 + 2, $product->refresh()->qty);
        $this->assertSame(2 * (float) $item['unit_price'], (float) $saleReturn->refresh()->total);
        $this->assertSame(1, $saleReturn->payments()->count());
        $this->assertSame(3, SaleReturnForm::remainingQtyForOrderItem($returnableItems[0]['order_item_id']));
    }

    public function test_sale_return_cannot_exceed_the_sold_qty(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $item = OrderForm::buildLineItemFromProduct($product, 3);

        $orderTest = Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => Customer::cashCustomer()->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_mode' => 'cod',
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = $orderTest->instance()->record;
        $returnableItems = SaleReturnForm::returnableItemsFor($order->id);
        $returnableItems[0]['qty'] = 10;

        Livewire::test(CreateSaleReturn::class)
            ->fillForm([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'returned_at' => now()->toDateTimeString(),
                'refund_mode' => 'cod',
                'items' => $returnableItems,
            ])
            ->call('create')
            ->assertHasFormErrors();
    }
}
