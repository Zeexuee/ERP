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
        Schema::create('material_melt_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_melt_batch_id')->constrained('material_melt_batches')->cascadeOnDelete();
            $table->date('weighed_date');
            $table->decimal('previous_weight', 10, 2);
            $table->decimal('new_weight', 10, 2);
            $table->decimal('evaporated_weight', 10, 2);
            $table->string('pic_name', 100);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_melt_logs');
    }
};
