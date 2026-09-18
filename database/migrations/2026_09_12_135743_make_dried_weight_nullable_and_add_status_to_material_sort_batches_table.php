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
        Schema::table('material_sort_batches', function (Blueprint $table) {
            $table->decimal('dried_weight', 10, 2)->nullable()->change();
            $table->decimal('drying_loss_weight', 10, 2)->nullable()->default(0)->change();
            $table->string('status', 30)->default('in_progress')->after('sort_code');
            $table->date('report_date')->nullable()->after('sort_date');
            $table->string('report_pic_name')->nullable()->after('pic_name');
            $table->string('report_signature_path')->nullable()->after('signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_sort_batches', function (Blueprint $table) {
            $table->dropColumn(['status', 'report_date', 'report_pic_name', 'report_signature_path']);
            $table->decimal('dried_weight', 10, 2)->nullable(false)->change();
            $table->decimal('drying_loss_weight', 10, 2)->nullable(false)->default(0)->change();
        });
    }
};
