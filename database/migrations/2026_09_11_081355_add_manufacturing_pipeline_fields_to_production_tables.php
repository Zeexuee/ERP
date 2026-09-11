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
        Schema::table('production_batches', function (Blueprint $table) {
            $table->string('finishing_type', 50)->nullable()->after('stage'); // molen, kerok, bor_vendor
            $table->decimal('wet_result_weight', 10, 2)->nullable()->after('actual_quantity'); // Hasil tembak basah (kg)
            $table->decimal('residual_resin_weight', 10, 2)->nullable()->after('wet_result_weight'); // Getah sisa tembak (kg)
            $table->decimal('dried_result_weight', 10, 2)->nullable()->after('residual_resin_weight'); // Hasil tembak setelah jemur (kg)
            $table->string('vendor_name', 100)->nullable()->after('pic_name'); // Vendor luar misal Pak Kholil (khusus KLM)
            $table->date('vendor_sent_date')->nullable()->after('vendor_name');
            $table->date('vendor_received_date')->nullable()->after('vendor_sent_date');
        });

        Schema::table('production_daily_logs', function (Blueprint $table) {
            $table->string('process_step', 50)->nullable()->after('stage'); // tembak, jemur, celup, cuci, warna, finishing
            $table->decimal('residual_resin_weight', 10, 2)->nullable()->after('work_status');
            $table->decimal('weighed_result_weight', 10, 2)->nullable()->after('residual_resin_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_daily_logs', function (Blueprint $table) {
            $table->dropColumn([
                'process_step',
                'residual_resin_weight',
                'weighed_result_weight',
            ]);
        });

        Schema::table('production_batches', function (Blueprint $table) {
            $table->dropColumn([
                'finishing_type',
                'wet_result_weight',
                'residual_resin_weight',
                'dried_result_weight',
                'vendor_name',
                'vendor_sent_date',
                'vendor_received_date',
            ]);
        });
    }
};
