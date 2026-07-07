<?php

namespace Tests\Feature\Filament;

use App\Services\InventoryItemsCsvParser;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class InventoryImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_inventory_csv_parser_builds_line_items_from_sku(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'inventory-import-');
        $handle = fopen($path, 'w');
        fputcsv($handle, InventoryItemsCsvParser::exampleHeaders());
        fputcsv($handle, InventoryItemsCsvParser::exampleRow());
        fclose($handle);

        $items = app(InventoryItemsCsvParser::class)->parse($path);

        unlink($path);

        $this->assertCount(1, $items);
        $this->assertSame(10, $items[0]['qty']);
        $this->assertSame(25.0, (float) $items[0]['purchase_rate']);
        $this->assertNotEmpty($items[0]['product_id']);
    }
}
