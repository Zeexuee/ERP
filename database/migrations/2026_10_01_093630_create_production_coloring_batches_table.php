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
        Schema::create('production_coloring_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->date('coloring_date');
            $table->string('pic_name');
            $table->text('notes')->nullable();

            // Reference to the material branch being processed
            $table->foreignId('material_branch_id')->constrained('material_branches')->cascadeOnDelete();

            // Total weight being processed
            $table->decimal('total_weight', 10, 2)->default(0);

            // Status of the coloring process
            $table->string('status')->default('in_progress');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_coloring_batches');
    }
};
