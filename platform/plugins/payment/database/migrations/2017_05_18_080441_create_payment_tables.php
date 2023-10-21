<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 120);
            $table->foreignId('user_id')->default(0);
            $table->string('charge_id', 60)->nullable();
            $table->string('payment_channel', 60)->nullable();
            $table->string('description', 255)->nullable();
            $table->decimal('amount', 15, 2)->unsigned();
            $table->foreignId('order_id')->nullable();
            $table->string('status', 60)->default('pending')->nullable();
            $table->string('payment_type')->default('confirm')->nullable();
            $table->foreignId('customer_id')->nullable();
            $table->decimal('refunded_amount', 15)->unsigned()->nullable();
            $table->string('refund_note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
