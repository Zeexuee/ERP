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
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->foreignId('warehouse_product_id')->nullable()->after('id')->constrained('warehouse_products')->nullOnDelete();
            $table->string('pic_name')->nullable()->after('current_stock');
            $table->longText('signature_path')->nullable()->after('pic_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->dropForeign(['warehouse_product_id']);
            $table->dropColumn(['warehouse_product_id', 'pic_name', 'signature_path']);
        });
    }
};
