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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('barcode')->unique();
            $table->string('sku')->unique();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('qty')->default(0);
            $table->unsignedBigInteger('security_stock')->default(0);
            $table->json('images')->nullable();
            $table->boolean('featured')->default(false);
            $table->decimal('mrp', 10, 2);
            $table->decimal('purchase_rate', 10, 2);
            $table->decimal('rate_a', 10, 2);
            $table->decimal('rate_b', 10, 2);
            $table->decimal('rate_c', 10, 2);
            $table->string('unit')->nullable();
            $table->decimal('unit_value', 10, 2)->default(0);
            $table->json('product_discounts')->nullable();
            $table->boolean('backorder')->default(false);
            $table->boolean('requires_shipping')->default(true);
            $table->date('published_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->longText('tags')->nullable();
            $table->uuid('brand_id')->index();
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreignId('tax_group_id')->nullable()->constrained('tax_groups')->nullOnDelete();
            $table->boolean('is_secondary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
