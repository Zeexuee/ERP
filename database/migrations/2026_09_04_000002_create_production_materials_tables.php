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
        // 1. Tabel Bahan Baku / Barang Gudang
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('category', 100);
            $table->string('unit', 50);
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->string('storage_location')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Penerimaan Barang Masuk
        Schema::create('material_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->string('source_or_supplier');
            $table->date('received_date');
            $table->string('received_by');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Batch Produksi / SPK Manufaktur
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 50)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('production_request_id')->nullable()->constrained('production_requests')->nullOnDelete();
            $table->decimal('target_quantity', 10, 2);
            $table->decimal('actual_quantity', 10, 2)->nullable();
            $table->string('unit', 50);
            $table->string('status', 30)->default('in_progress'); // draft, in_progress, completed, cancelled
            $table->string('stage', 100)->default('Persiapan Bahan & Sortir Kayu');
            $table->date('start_date');
            $table->date('target_completion_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('pic_name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Tabel Alokasi Bahan Baku per Batch Produksi (BOM)
        Schema::create('production_batch_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('quantity_used', 10, 2);
            $table->string('unit', 50);
            $table->timestamps();
        });

        // 5. Tabel Laporan Proses Harian
        Schema::create('production_daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->date('log_date');
            $table->string('stage', 100);
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->string('pic_name');
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_daily_logs');
        Schema::dropIfExists('production_batch_materials');
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('material_receipts');
        Schema::dropIfExists('materials');
    }
};
