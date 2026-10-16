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
        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // Stock Keeping Unit
            $table->string('barcode')->unique();
            $table->string('qr_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kg, m, etc.
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions')->nullable(); // L x W x H
            $table->integer('min_stock')->default(0); // Low stock alert threshold
            $table->integer('max_stock')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->foreignId('warehouse_branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_location_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('current_stock')->default(0);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->json('custom_fields')->nullable(); // For extensibility
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['warehouse_branch_id', 'current_stock'], 'wh_items_branch_stock_idx');
            $table->index('barcode');
            $table->index('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_items');
    }
};
