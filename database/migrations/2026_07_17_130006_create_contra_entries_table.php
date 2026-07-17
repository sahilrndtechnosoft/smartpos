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
        Schema::create('contra_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 32)->unique();
            $table->timestamp('entry_date');
            $table->foreignUuid('from_ledger_account_id')->constrained('ledger_accounts');
            $table->foreignUuid('to_ledger_account_id')->constrained('ledger_accounts');
            $table->decimal('amount', 12, 2);
            $table->string('narration')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contra_entries');
    }
};
