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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('code', 32)->unique();
            $table->timestamp('ordered_at')->nullable();

            $table->string('payment_mode')->default('cod');
        
            $table->decimal('total', 12, 2)->nullable();   // before discount
            $table->decimal('discount_total', 12, 2)->default(0);   // item before             
            $table->decimal('grand_total', 12, 2)->nullable();

            $table->decimal('secondary_total', 12, 2)->nullable();
            $table->decimal('primary_total', 12, 2)->nullable();

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
        Schema::dropIfExists('orders');
    }
};
