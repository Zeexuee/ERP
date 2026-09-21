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
        Schema::create('production_tembak_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_tembak_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['wood', 'resin']);
            $table->decimal('weight', 10, 2);
            $table->decimal('unit_cost', 15, 2)->default(0); // Menyimpan harga modal pada saat itu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_tembak_materials');
    }
};
