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
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('unit')->default('PCS');
            $table->integer('pieces_per_box')->default(1);
            $table->string('hsn_code')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('track_expiry')->default(false);
            $table->integer('reorder_level')->default(0);
            $table->integer('reorder_qty')->default(0);
            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_secondary')->default(false);
            $table->boolean('is_active')->default(true);

            // ✅ unsignedBigInteger instead of foreignId() — avoids FK constraint against users
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('category');
            $table->index('barcode');
            $table->index('is_active');
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
