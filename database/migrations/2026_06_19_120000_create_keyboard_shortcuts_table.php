<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyboard_shortcuts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('combination')->unique();
            $table->string('action_type');
            $table->string('action_target')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sr')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyboard_shortcuts');
    }
};
