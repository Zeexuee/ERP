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
        Schema::table('warehouse_stock_movements', function (Blueprint $table) {
            $table->string('pic_name')->nullable()->after('notes');
            $table->longText('signature_path')->nullable()->after('pic_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_stock_movements', function (Blueprint $table) {
            $table->dropColumn(['pic_name', 'signature_path']);
        });
    }
};
