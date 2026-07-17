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
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['bank', 'cash', 'expense', 'income', 'sundry_debtor', 'sundry_creditor']);
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->boolean('is_system')->default(false);
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
        Schema::dropIfExists('ledger_accounts');
    }
};
