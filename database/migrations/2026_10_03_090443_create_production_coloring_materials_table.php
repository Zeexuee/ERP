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
        Schema::create('production_coloring_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_coloring_batch_id')->constrained('production_coloring_batches', indexName: 'pcm_batch_id_fk')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 10, 2);
            $table->timestamps();
        });

        Schema::table('production_coloring_batches', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('notes');
            $table->string('report_signature_path')->nullable()->after('report_notes');
            $table->string('report_image_path')->nullable()->after('report_signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_coloring_batches', function (Blueprint $table) {
            $table->dropColumn(['signature_path', 'report_signature_path', 'report_image_path']);
        });

        Schema::dropIfExists('production_coloring_materials');
    }
};
