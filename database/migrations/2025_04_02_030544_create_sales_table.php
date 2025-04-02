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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 20)->unique();
            $table->date('sale_date');
            $table->integer('total_amount');
            $table->integer('payment_amount');
            $table->integer('change_amount')->default(0);
            $table->integer('cost_amount')->default(0);
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('customer_name', 50)->default('Umum');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};