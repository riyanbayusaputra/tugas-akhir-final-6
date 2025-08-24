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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number', 50)->unique();
            $table->integer('subtotal')->default(0);
            $table->integer('total_amount');
            $table->enum('status', ['checking', 'pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('checking');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            
            // Shipping Information
            $table->string('recipient_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->integer('shipping_cost')->default(0);
            $table->string('shipping_address', 255)->nullable();
         
            // Payment Gateway
            $table->string('payment_gateway_transaction_id', 100)->nullable();
            $table->longText('payment_gateway_data')->nullable();

            $table->string('noted', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
