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
   Schema::create('bond_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bond_id')->constrained()->onDelete('cascade');
    $table->string('item_description');
    $table->decimal('quantity', 10, 2);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bond_items');
    }
};
