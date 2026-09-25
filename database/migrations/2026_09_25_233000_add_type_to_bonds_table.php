<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bonds', function (Blueprint $table) {
            $table->string('type', 20)->default('receipt')->after('stack_id');
            $table->dropUnique('bonds_bond_serial_unique');
            $table->unique(['type', 'bond_serial'], 'bonds_type_serial_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonds', function (Blueprint $table) {
            $table->dropUnique('bonds_type_serial_unique');
            $table->unique('bond_serial', 'bonds_bond_serial_unique');
            $table->dropColumn('type');
        });
    }
};
