<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hsn_code')->nullable()->after('barcode');
            $table->string('secondary_unit')->nullable()->after('unit_value');
            $table->decimal('secondary_unit_qty', 10, 2)->nullable()->after('secondary_unit');
            $table->unsignedInteger('scheme_buy_qty')->nullable()->after('product_discounts');
            $table->unsignedInteger('scheme_free_qty')->nullable()->after('scheme_buy_qty');
            $table->uuid('scheme_free_product_id')->nullable()->after('scheme_free_qty');
            $table->foreign('scheme_free_product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['scheme_free_product_id']);
            $table->dropColumn([
                'hsn_code',
                'secondary_unit',
                'secondary_unit_qty',
                'scheme_buy_qty',
                'scheme_free_qty',
                'scheme_free_product_id',
            ]);
        });
    }
};
