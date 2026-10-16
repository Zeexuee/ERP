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
        Schema::create('warehouse_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->default('botol');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->string('category')->nullable(); // Kategori wewangian / parfum
            $table->text('description')->nullable(); // Detail barang / aroma profile
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_products');
    }
};
