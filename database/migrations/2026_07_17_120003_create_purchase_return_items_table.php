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
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_return_id')->constrained('purchase_returns');
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items');
            $table->foreignUuid('product_id')->constrained('products');
            $table->string('product_name');
            $table->integer('qty');
            $table->decimal('purchase_rate', 12, 2);
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
        Schema::dropIfExists('purchase_return_items');
    }
};
