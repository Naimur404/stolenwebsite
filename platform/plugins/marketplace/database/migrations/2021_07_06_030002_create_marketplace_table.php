<?php

use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('mp_vendor_info');
        Schema::dropIfExists('mp_customer_revenues');
        Schema::dropIfExists('mp_customer_withdrawals');

        Schema::table('ec_orders', function (Blueprint $table) {
            if (Schema::hasColumn('ec_orders', 'store_id')) {
                $table->dropColumn('store_id');
            }
        });

        Schema::table('ec_discounts', function (Blueprint $table) {
            if (Schema::hasColumn('ec_discounts', 'store_id')) {
                $table->dropColumn('store_id');
            }
        });

        Schema::table('ec_products', function (Blueprint $table) {
            if (Schema::hasColumn('ec_products', 'store_id')) {
                $table->dropColumn('store_id');
            }
        });

        Schema::table('ec_customers', function (Blueprint $table) {
            if (Schema::hasColumn('ec_customers', 'is_vendor')) {
                $table->dropColumn('is_vendor');
            }

            if (Schema::hasColumn('ec_customers', 'balance')) {
                $table->dropColumn('balance');
            }

            if (Schema::hasColumn('ec_customers', 'vendor_info_id')) {
                $table->dropColumn('vendor_info_id');
            }
        });

        Schema::dropIfExists('mp_stores');

        Schema::create('mp_stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 60)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('country', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->foreignId('customer_id')->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('description', 400)->nullable();
            $table->longText('content')->nullable();
            $table->string('status', 60)->default('published');
            $table->dateTime('vendor_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::table('ec_products', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable();
        });

        Schema::create('mp_vendor_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->default(0);
            $table->decimal('balance', 15)->default(0);
            $table->decimal('total_fee', 15)->default(0);
            $table->decimal('total_revenue', 15)->default(0);
            $table->string('signature', 255)->nullable();
            $table->text('bank_info')->nullable();
            $table->timestamps();
        });

        Schema::table('ec_customers', function (Blueprint $table) {
            $table->boolean('is_vendor')->default(false);
        });

        Schema::table('ec_orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable();
        });

        Schema::table('ec_discounts', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable();
        });

        Schema::create('mp_customer_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->decimal('sub_amount', 15)->default(0)->unsigned()->nullable();
            $table->decimal('fee', 15)->default(0)->unsigned()->nullable();
            $table->decimal('amount', 15)->default(0)->unsigned()->nullable();
            $table->decimal('current_balance', 15)->default(0)->unsigned()->nullable();
            $table->string('currency', 120)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('mp_customer_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable();
            $table->decimal('fee', 15)->default(0)->unsigned()->nullable();
            $table->decimal('amount', 15)->default(0)->unsigned()->nullable();
            $table->decimal('current_balance', 15)->default(0)->unsigned()->nullable();
            $table->string('currency', 120)->nullable();
            $table->text('description')->nullable();
            $table->text('bank_info')->nullable();
            $table->string('payment_channel', 60)->nullable();
            $table->foreignId('user_id')->default(0);
            $table->string('status', 60)->default(WithdrawalStatusEnum::PENDING);
            $table->text('images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_vendor_info');
        Schema::dropIfExists('mp_customer_revenues');
        Schema::dropIfExists('mp_customer_withdrawals');

        Schema::table('ec_orders', function (Blueprint $table) {
            if (Schema::hasColumn('ec_orders', 'store_id')) {
                $table->dropColumn('store_id');
            }
        });

        Schema::table('ec_products', function (Blueprint $table) {
            if (Schema::hasColumn('ec_products', 'store_id')) {
                $table->dropColumn('store_id');
            }
        });

        Schema::table('ec_customers', function (Blueprint $table) {
            if (Schema::hasColumn('ec_customers', 'is_vendor')) {
                $table->dropColumn('is_vendor');
            }

            if (Schema::hasColumn('ec_customers', 'balance')) {
                $table->dropColumn('balance');
            }

            if (Schema::hasColumn('ec_customers', 'vendor_info_id')) {
                $table->dropColumn('vendor_info_id');
            }
        });

        Schema::dropIfExists('mp_stores');
    }
};
