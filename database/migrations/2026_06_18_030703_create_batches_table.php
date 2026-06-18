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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('barcode')->nullable()->unique();
            $table->date('expiry_date')->nullable();
            $table->decimal('mrp', 10, 2);
            $table->decimal('purchase_rate', 10, 2);
            $table->decimal('rate_a', 10, 2);
            $table->decimal('rate_b', 10, 2);
            $table->decimal('rate_c', 10, 2);
            $table->boolean('is_secondary')->default(false);
            $table->integer('stock_primary')->default(0);
            $table->integer('stock_secondary')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('barcode');
            $table->index('expiry_date');
            $table->index('is_secondary');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_items');
        Schema::dropIfExists('batches');
    }
};
