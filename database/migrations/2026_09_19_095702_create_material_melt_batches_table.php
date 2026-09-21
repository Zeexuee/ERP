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
        Schema::create('material_melt_batches', function (Blueprint $table) {
            $table->id();
            $table->string('melt_code', 20)->unique();
            $table->foreignId('source_material_id')->constrained('materials');
            $table->decimal('initial_weight', 10, 2); // berat padat awal
            $table->foreignId('target_material_id')->nullable()->constrained('materials');
            $table->decimal('initial_liquid_weight', 10, 2)->nullable(); // berat cair awal saat dilaporkan
            $table->decimal('current_weight', 10, 2)->nullable(); // berat cair saat ini (setelah susut)
            $table->string('status', 20)->default('in_progress'); // in_progress, monitoring, completed
            $table->date('melt_date');
            $table->string('pic_name', 100);
            $table->text('notes')->nullable();

            // Kolom untuk pelaporan pencairan
            $table->date('report_date')->nullable();
            $table->string('report_pic_name', 100)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_melt_batches');
    }
};
