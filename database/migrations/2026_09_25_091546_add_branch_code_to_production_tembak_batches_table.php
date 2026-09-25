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
            // Identitas branch produksi. Lahir di sini lalu terbawa ke gudang,
            // finishing, dan akhirnya ke barang jadi siap jual.
            $table->string('branch_code', 50)->nullable()->after('tembak_code');
            $table->index('branch_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_tembak_batches', function (Blueprint $table) {
            $table->dropIndex(['branch_code']);
            $table->dropColumn('branch_code');
        });
    }
};
