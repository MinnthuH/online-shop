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
            $table->integer('shop_id')->nullable();
            $table->integer('user_id');
            $table->integer('customer_id');
            $table->integer('deli_id')->nullable();
            $table->string('invoice_date');
            $table->string('invoice_no')->unique();
            $table->string('payment_type')->nullable();
            $table->string('sub_total')->nullable();
            $table->integer('transprot_id')->nullable();
            $table->string('total')->nullable();
            $table->string('capital')->nullable();
            $table->string('discount')->nullable();
            $table->string('accepted_ammount')->nullable();
            $table->string('due')->nullable();
            $table->string('return_change')->nullable();
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
