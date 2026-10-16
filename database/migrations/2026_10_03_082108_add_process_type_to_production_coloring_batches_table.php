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
            $table->string('process_type')->after('batch_code')->default('celup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_coloring_batches', function (Blueprint $table) {
            $table->dropColumn('process_type');
        });
    }
};
