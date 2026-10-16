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
        Schema::create('warehouse_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_branch_id')->constrained();
            $table->foreignId('warehouse_location_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['in', 'out', 'transfer', 'adjustment']); // Movement type
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference_type')->nullable(); // sales_order, production_request, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained(); // Who performed the movement
            $table->timestamp('movement_date');
            $table->timestamps();

            $table->index(['warehouse_item_id', 'movement_date'], 'wh_stock_mov_item_date_idx');
            $table->index(['warehouse_branch_id', 'type'], 'wh_stock_mov_branch_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_movements');
    }
};
