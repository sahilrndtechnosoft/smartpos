<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keyboard_shortcuts', function (Blueprint $table) {
            $table->string('action_record_id')->nullable()->after('action_target');
        });
    }

    public function down(): void
    {
        Schema::table('keyboard_shortcuts', function (Blueprint $table) {
            $table->dropColumn('action_record_id');
        });
    }
};
