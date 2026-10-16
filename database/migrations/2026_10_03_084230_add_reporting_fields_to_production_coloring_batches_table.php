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
        Schema::table('production_coloring_batches', function (Blueprint $table) {
            $table->decimal('result_weight', 10, 2)->nullable()->after('total_weight');
            $table->decimal('loss_weight', 10, 2)->nullable()->after('result_weight');
            $table->date('report_date')->nullable()->after('status');
            $table->string('report_pic_name')->nullable()->after('report_date');
            $table->text('report_notes')->nullable()->after('report_pic_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_coloring_batches', function (Blueprint $table) {
            $table->dropColumn([
                'result_weight',
                'loss_weight',
                'report_date',
                'report_pic_name',
                'report_notes',
            ]);
        });
    }
};
