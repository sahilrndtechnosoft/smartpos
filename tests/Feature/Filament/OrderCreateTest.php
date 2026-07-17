<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Customer;
use App\Models\Order;
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

    public function test_order_defaults_to_the_cash_customer(): void
    {
        $cashCustomer = Customer::cashCustomer();

        Livewire::test(CreateOrder::class)
            ->assertSet('data.customer_id', $cashCustomer->id);
    }

    public function test_credit_payment_mode_creates_no_payment_record(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $item = OrderForm::buildLineItemFromProduct($product, 1);

        $test = Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => Customer::cashCustomer()->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_mode' => 'credit',
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(0, $test->instance()->record->payments()->count());
    }

    public function test_multi_payment_mode_requires_amounts_to_match_grand_total(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $item = OrderForm::buildLineItemFromProduct($product, 1);
        $grandTotal = (float) $item['final_price'];
        $ordersBefore = Order::query()->count();

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => Customer::cashCustomer()->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_mode' => 'multi',
                'items' => [$item],
                'splitPayments' => [
                    ['method' => 'cod', 'amount' => 1],
                ],
            ])
            ->call('create');

        $this->assertSame($ordersBefore, Order::query()->count());

        $test = Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => Customer::cashCustomer()->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_mode' => 'multi',
                'items' => [$item],
                'splitPayments' => [
                    ['method' => 'cod', 'amount' => $grandTotal / 2],
                    ['method' => 'upi', 'amount' => $grandTotal / 2],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $test->instance()->record->payments()->count());
    }

    public function test_qty_wise_rate_slab_overrides_flat_rate(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $product->update([
            'product_discounts' => [
                ['rate_type' => 'rate_a', 'qty' => 12, 'price' => 50],
                ['rate_type' => 'rate_a', 'qty' => 24, 'price' => 45],
            ],
        ]);
        $product->refresh();

        $this->assertSame((float) $product->rate_a, \App\Support\ProductRateOptions::priceFor($product, 'rate_a', 5));
        $this->assertSame(50.0, \App\Support\ProductRateOptions::priceFor($product, 'rate_a', 12));
        $this->assertSame(50.0, \App\Support\ProductRateOptions::priceFor($product, 'rate_a', 20));
        $this->assertSame(45.0, \App\Support\ProductRateOptions::priceFor($product, 'rate_a', 24));
    }

    public function test_free_scheme_adds_an_auto_line_when_threshold_is_met(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $product->update(['scheme_buy_qty' => 12, 'scheme_free_qty' => 1]);
        $product->refresh();

        $item = OrderForm::buildLineItemFromProduct($product, 24);

        $items = OrderForm::applySchemes([$item]);

        $this->assertCount(2, $items);
        $this->assertSame(2, $items[1]['qty']);
        $this->assertSame(0.0, (float) $items[1]['unit_price']);
        $this->assertTrue(data_get($items[1], 'product_snapshot.is_scheme_free'));

        // Dropping below the threshold removes the free line again.
        $items[0]['qty'] = 6;
        $items = OrderForm::applySchemes($items);

        $this->assertCount(1, $items);
    }

    public function test_sale_decrements_product_stock(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $stockBefore = $product->qty;
        $item = OrderForm::buildLineItemFromProduct($product, 3);

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => Customer::cashCustomer()->id,
                'ordered_at' => now()->toDateTimeString(),
                'payment_mode' => 'cod',
                'items' => [$item],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($stockBefore - 3, $product->refresh()->qty);
    }
}
