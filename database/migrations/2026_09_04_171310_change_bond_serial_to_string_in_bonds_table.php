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
            // Change from INT to VARCHAR(20) to preserve leading zeros
            $table->string('bond_serial', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bonds', function (Blueprint $table) {
            $table->integer('bond_serial')->change();
        });
    }
};
