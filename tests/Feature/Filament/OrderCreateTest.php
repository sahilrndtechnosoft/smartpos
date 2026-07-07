<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCreateTest extends TestCase
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

    public function test_order_create_applies_zero_totals_when_no_line_items(): void
    {
        $customer = Customer::query()->firstOrFail();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_mode' => 'cod',
                'items' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'discount_total' => 0,
            'total' => 0,
            'grand_total' => 0,
        ]);
    }

    public function test_barcode_scan_adds_product_line_item(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();

        Livewire::test(CreateOrder::class)
            ->call('scanBarcode', $product->barcode)
            ->assertSet('data.items.0.product_id', $product->id)
            ->assertSet('data.items.0.qty', 1);

        Livewire::test(CreateOrder::class)
            ->call('scanBarcode', $product->barcode)
            ->call('scanBarcode', $product->barcode)
            ->assertSet('data.items.0.qty', 2);
    }

    public function test_order_totals_are_calculated_from_line_items(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $item = OrderForm::buildLineItemFromProduct($product, 2);

        $totals = OrderForm::calculateOrderTotals([$item]);

        $this->assertSame(2 * (float) $item['unit_price'], $totals['total']);
        $this->assertSame(0.0, $totals['discount_total']);
        $this->assertSame($totals['total'], $totals['grand_total']);
    }
}
