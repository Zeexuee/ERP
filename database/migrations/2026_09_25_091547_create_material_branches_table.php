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
        // Buku besar identitas branch per barang gudang. Satu barang bisa memuat
        // stok dari beberapa branch sekaligus (barang gabungan).
        Schema::create('material_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->string('branch_code', 50);
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('source_type', 30); // tembak, finishing
            $table->string('source_reference', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['material_id', 'branch_code']);
            $table->index('branch_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_branches');
    }
};
