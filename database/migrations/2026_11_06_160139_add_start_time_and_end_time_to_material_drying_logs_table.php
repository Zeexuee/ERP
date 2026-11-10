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
        Schema::table('material_drying_logs', function (Blueprint $table) {
            $table->string('start_time', 10)->nullable()->after('start_date');
            $table->string('end_time', 10)->nullable()->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_drying_logs', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
