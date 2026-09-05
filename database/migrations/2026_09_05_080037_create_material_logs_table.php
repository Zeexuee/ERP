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
        Schema::create('material_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->string('type'); // 'in', 'out', 'recount'
            $table->string('reference_number')->nullable();
            $table->decimal('quantity', 15, 2);
            $table->string('unit');
            $table->string('actor_by')->nullable();
            $table->string('source_or_destination')->nullable();
            $table->timestamp('movement_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_logs');
    }
};
