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
        Schema::create('production_tembak_batches', function (Blueprint $table) {
            $table->id();
            $table->string('tembak_code', 50)->unique();
            $table->foreignId('wood_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('wood_weight', 10, 2);
            $table->foreignId('resin_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('resin_weight', 10, 2);
            $table->decimal('wet_result_weight', 10, 2)->nullable();
            $table->decimal('residual_resin_weight', 10, 2)->nullable();
            $table->foreignId('residual_resin_material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->decimal('dried_result_weight', 10, 2)->nullable();
            $table->foreignId('output_material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('status', 30)->default('in_progress');
            $table->date('tembak_date');
            $table->date('report_date')->nullable();
            $table->string('pic_name', 100);
            $table->string('report_pic_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->text('report_notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_tembak_batches');
    }
};
