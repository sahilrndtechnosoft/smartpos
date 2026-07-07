<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\User;
use App\Services\PrintStoreDetails;
use App\Services\SettingStore;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class PrintDocumentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_print_store_details_use_contact_and_company_settings(): void
    {
        SettingStore::set('contact_details', [
            'shop_name' => 'GharGrocer Print Test',
            'email' => 'billing@ghargrocer.com',
            'primary_phone' => '9913705841',
            'website_url' => 'https://ghargrocer.com',
            'address' => 'Vapi, Gujarat',
            'google_map_address' => '',
            'other_phones' => [],
            'other_emails' => [],
        ], 'contact');

        SettingStore::set('company_details', [
            'firm_pan_number' => 'TESTPAN1234',
            'gst_number' => '24TESTGST1234Z5',
            'fssai_license' => '12345678901234',
        ], 'company');

        $store = PrintStoreDetails::resolve();

        $this->assertSame('GharGrocer Print Test', $store['name']);
        $this->assertSame('billing@ghargrocer.com', $store['email']);
        $this->assertSame('9913705841', $store['phone']);
        $this->assertSame('Vapi, Gujarat', $store['address']);
        $this->assertSame('24TESTGST1234Z5', $store['gst_number']);
        $this->assertSame('TESTPAN1234', $store['pan_number']);
    }

    public function test_inventory_print_invoice_shows_company_details_from_settings(): void
    {
        $admin = User::query()->where('email', 'admin@smartpos.local')->firstOrFail();
        $inventory = Inventory::query()->with('supplier')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('print.inventories.invoice', $inventory));

        $response->assertOk();
        $response->assertSee('GharGrocer', false);
        $response->assertSee('24ABCDE1234F1Z5', false);
        $response->assertSee('ABCDE1234F', false);
    }
}
