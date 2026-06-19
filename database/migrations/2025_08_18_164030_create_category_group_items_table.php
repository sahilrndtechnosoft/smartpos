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
        Schema::create('category_group_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('category_group_id')->nullable()->constrained('category_groups');
            $table->foreignUuid('category_id')->nullable()->constrained('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_group_items');
    }
};
