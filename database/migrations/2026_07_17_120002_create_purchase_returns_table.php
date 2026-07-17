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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_id')->constrained('inventories');
            $table->foreignUuid('supplier_id')->constrained('suppliers');
            $table->string('code', 32)->unique();
            $table->timestamp('returned_at')->nullable();
            $table->string('refund_mode')->default('cod');
            $table->text('reason')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
