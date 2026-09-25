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
        // 1. Sesi finishing. Urutan prosesnya tidak pasti, jadi sesi hanya menyimpan
        //    posisi berat terkini dan baru selesai bila admin menyatakan selesai.
        Schema::create('production_finishing_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('finishing_code', 50)->unique();
            $table->string('branch_code', 50)->nullable();
            $table->boolean('branch_is_merged')->default(false);
            $table->json('source_branch_codes')->nullable();
            $table->foreignId('source_material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('initial_weight', 10, 2);
            $table->decimal('current_weight', 10, 2);
            $table->decimal('output_weight', 10, 2)->nullable();
            $table->string('status', 30)->default('in_progress'); // in_progress, completed
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_branch_id')->nullable()->constrained('product_branches')->nullOnDelete();
            $table->integer('output_quantity')->nullable();
            $table->date('finishing_date');
            $table->date('completed_date')->nullable();
            $table->string('pic_name', 100);
            $table->string('completion_pic_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('completion_signature_path')->nullable();
            $table->timestamps();

            $table->index('branch_code');
            $table->index('status');
        });

        // 2. Riwayat setiap proses finishing (celup, molen, kerok, bor) beserta
        //    ampas buangannya.
        Schema::create('production_finishing_steps', function (Blueprint $table) {
            $table->id();
            // Nama constraint dipendekkan manual karena MySQL membatasi
            // panjang identifier maksimal 64 karakter.
            $table->foreignId('production_finishing_session_id')
                ->constrained(
                    table: 'production_finishing_sessions',
                    indexName: 'fin_steps_session_id_foreign'
                )
                ->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('process_type', 30); // celup, molen, kerok, bor
            $table->date('step_date');
            $table->decimal('weight_before', 10, 2);
            $table->decimal('weight_after', 10, 2);
            $table->decimal('waste_weight', 10, 2)->default(0);
            $table->foreignId('waste_material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->decimal('material_cost', 15, 2)->default(0);
            $table->string('pic_name', 100);
            $table->text('notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();

            $table->index(['production_finishing_session_id', 'sequence'], 'fin_steps_session_sequence_index');
        });

        // 3. Bahan yang dipakai pada setiap proses finishing beserta harganya,
        //    contohnya metanol pada proses celup.
        Schema::create('production_finishing_step_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_finishing_step_id')
                ->constrained(
                    table: 'production_finishing_steps',
                    indexName: 'fin_step_materials_step_id_foreign'
                )
                ->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_finishing_step_materials');
        Schema::dropIfExists('production_finishing_steps');
        Schema::dropIfExists('production_finishing_sessions');
    }
};
