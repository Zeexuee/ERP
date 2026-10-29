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
        Schema::create('warehouse_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->string('borrower_name');
            $table->string('borrower_contact')->nullable();
            $table->string('purpose'); // Pajangan Etalase, Sample, Pameran, dll
            $table->date('loan_date');
            $table->date('expected_return_date')->nullable();
            $table->string('status')->default('borrowed'); // borrowed, partial, returned
            $table->text('notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('returned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_loan_id')->constrained('warehouse_loans')->cascadeOnDelete();
            $table->foreignId('warehouse_item_id')->constrained('warehouse_items')->restrictOnDelete();
            $table->integer('quantity_loaned');
            $table->integer('quantity_returned')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_loan_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_loan_id')->constrained('warehouse_loans')->cascadeOnDelete();
            $table->string('return_number')->unique();
            $table->dateTime('return_date');
            $table->string('receiver_name');
            $table->string('signature_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('warehouse_loan_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_loan_return_id')->constrained('warehouse_loan_returns')->cascadeOnDelete();
            $table->foreignId('warehouse_loan_item_id')->constrained('warehouse_loan_items')->cascadeOnDelete();
            $table->foreignId('warehouse_item_id')->constrained('warehouse_items')->restrictOnDelete();
            $table->integer('quantity_returned');
            $table->string('condition')->default('good'); // good, damaged, lost
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_loan_return_items');
        Schema::dropIfExists('warehouse_loan_returns');
        Schema::dropIfExists('warehouse_loan_items');
        Schema::dropIfExists('warehouse_loans');
    }
};
