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
    // Ensure 'Blueprint' is type-hinted, not 'Table'
    Schema::create('bonds', function (Blueprint $table) { 
        $table->id();
        $table->foreignId('stack_id')->nullable()->constrained();
        $table->integer('bond_serial')->unique();
        $table->date('date');
        $table->string('operation_name');
        $table->string('received_from');
        $table->string('car_number')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonds');
    }
};
