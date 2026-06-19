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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('sr')->default(0);
            $table->foreignUuid('inventory_id')->constrained('inventories');
            $table->foreignUuid('product_id')->constrained('products');
            $table->integer('qty')->default(0);
            $table->decimal('mrp', 10, 2);
            $table->decimal('purchase_rate', 10, 2);
            $table->decimal('rate_a', 10, 2);
            $table->decimal('rate_b', 10, 2);
            $table->decimal('rate_c', 10, 2);
            $table->date('expiry_date')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
