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
        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->dropForeign(['wood_material_id']);
            $table->dropForeign(['resin_material_id']);
            $table->dropColumn(['wood_material_id', 'wood_weight', 'resin_material_id', 'resin_weight']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->foreignId('wood_material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->decimal('wood_weight', 10, 2)->nullable();
            $table->foreignId('resin_material_id')->nullable()->constrained('materials')->cascadeOnDelete();
            $table->decimal('resin_weight', 10, 2)->nullable();
        });
    }
};
