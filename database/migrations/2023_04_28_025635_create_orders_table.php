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
            $table->integer('customer_id');
            $table->integer('user_id');
            $table->integer('shop_id');
            $table->string('order_date');
            $table->string('order_status');
            $table->string('total_products')->nullable();
            $table->string('sub_total')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('total')->nullable();
            $table->string('capital')->nullable();
            $table->string('paymet_status')->nullable();
            $table->string('pay')->nullable();
            $table->string('due')->nullable();
            $table->timestamps();
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
