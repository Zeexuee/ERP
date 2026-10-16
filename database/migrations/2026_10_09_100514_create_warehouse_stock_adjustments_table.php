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
        Schema::create('warehouse_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_branch_id')->constrained();
            $table->enum('reason', ['damaged', 'lost', 'found', 'expired', 'audit', 'other']);
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('adjustment_quantity'); // Can be negative or positive
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained(); // Who made the adjustment
            $table->timestamp('adjustment_date');
            $table->timestamps();

            $table->index(['warehouse_item_id', 'adjustment_date'], 'wh_stock_adj_item_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_adjustments');
    }
};
