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
        Schema::table('production_daily_logs', function (Blueprint $table) {
            $table->string('work_status')->default('selesai')->after('stage');
            $table->string('attachment_path')->nullable()->after('pic_name');
            $table->string('signature_path')->nullable()->after('attachment_path');
            $table->integer('progress_percentage')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_daily_logs', function (Blueprint $table) {
            $table->dropColumn(['work_status', 'attachment_path', 'signature_path']);
        });
    }
};
