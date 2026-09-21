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
        Schema::create('material_carve_batches', function (Blueprint $table) {
            $table->id();
            $table->string('carve_code', 50)->unique();
            $table->string('status', 30)->default('in_progress');
            $table->foreignId('source_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('initial_weight', 10, 2);
            $table->decimal('dried_weight', 10, 2)->nullable();
            $table->decimal('drying_loss_weight', 10, 2)->nullable()->default(0);
            $table->date('carve_date');
            $table->string('pic_name');
            $table->date('report_date')->nullable();
            $table->string('report_pic_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('report_signature_path')->nullable();
            $table->timestamps();
        });

        Schema::create('material_carve_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_carve_batch_id')->constrained('material_carve_batches')->cascadeOnDelete();
            $table->foreignId('target_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('result_weight', 10, 2);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_carve_items');
        Schema::dropIfExists('material_carve_batches');
    }
};
