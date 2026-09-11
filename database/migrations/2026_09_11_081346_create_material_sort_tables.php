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
        Schema::create('material_sort_batches', function (Blueprint $table) {
            $table->id();
            $table->string('sort_code', 50)->unique();
            $table->foreignId('source_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('initial_weight', 10, 2); // Misal beli KCB 20 kg
            $table->decimal('dried_weight', 10, 2);   // Hasil jemur misal 18 kg
            $table->decimal('drying_loss_weight', 10, 2)->default(0); // Susut jemur misal 2 kg
            $table->date('sort_date');
            $table->string('pic_name');
            $table->text('notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();
        });

        Schema::create('material_sort_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_sort_batch_id')->constrained('material_sort_batches')->cascadeOnDelete();
            $table->foreignId('target_material_id')->constrained('materials')->cascadeOnDelete(); // Bahan tembak (SP, Mini SP, Alami, Afkir)
            $table->decimal('result_weight', 10, 2); // Kuantitas hasil sortir
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_sort_items');
        Schema::dropIfExists('material_sort_batches');
    }
};
