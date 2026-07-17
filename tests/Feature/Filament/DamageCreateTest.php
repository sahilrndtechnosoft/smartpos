<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Damages\Pages\CreateDamage;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class DamageCreateTest extends TestCase
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

    public function test_damage_decrements_stock_and_computes_cost_value(): void
    {
        $product = Product::query()->where('sku', 'AMUL-MILK-1L')->firstOrFail();
        $stockBefore = $product->qty;

        $test = Livewire::test(CreateDamage::class)
            ->fillForm([
                'product_id' => $product->id,
                'qty' => 4,
                'damaged_at' => now()->toDateTimeString(),
                'reason' => 'Breakage',
                'cost_value' => 4 * (float) $product->purchase_rate,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $damage = $test->instance()->record;

        $this->assertSame($stockBefore - 4, $product->refresh()->qty);
        $this->assertSame(round(4 * (float) $product->purchase_rate, 2), (float) $damage->cost_value);
    }
}
