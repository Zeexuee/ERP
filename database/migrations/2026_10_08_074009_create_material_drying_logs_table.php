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
        Schema::create('material_drying_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('previous_weight', 10, 2);
            $table->decimal('new_weight', 10, 2)->nullable();
            $table->decimal('shrinkage_weight', 10, 2)->nullable();
            $table->string('pic_name', 100);
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('menunggu'); // menunggu, selesai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_drying_logs');
    }
};
