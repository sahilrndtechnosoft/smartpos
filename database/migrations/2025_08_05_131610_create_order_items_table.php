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
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->foreignUuid('product_id')->references('id')->on('products');

            $table->string('product_name'); 
            $table->integer('qty');
            $table->decimal('unit_price', 10, 2);  
            $table->decimal('subtotal', 10, 2)->default(0); // qty * unit_price

            // Discounts
            $table->json('discount_rule')->nullable();
            $table->enum('discount_type', ['fix', 'percent'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();

            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('final_price', 12, 2)->default(0);

            $table->decimal('secondary_total', 12, 2)->nullable();
            $table->decimal('primary_total', 12, 2)->nullable();

            $table->json('product_snapshot')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
