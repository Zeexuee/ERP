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
        Schema::create('production_tembak_drying_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_tembak_batch_id')
                ->constrained('production_tembak_batches')
                ->cascadeOnDelete();
            $table->date('weighed_date');
            $table->decimal('previous_weight', 10, 2);
            $table->decimal('new_weight', 10, 2);
            $table->decimal('shrinkage_weight', 10, 2);
            $table->string('pic_name', 100);
            $table->text('notes')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_tembak_drying_logs');
    }
};
