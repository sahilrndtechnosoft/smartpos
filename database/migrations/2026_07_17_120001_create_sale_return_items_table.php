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
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sale_return_id')->constrained('sale_returns');
            $table->foreignUuid('order_item_id')->constrained('order_items');
            $table->foreignUuid('product_id')->constrained('products');
            $table->string('product_name');
            $table->integer('qty');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
