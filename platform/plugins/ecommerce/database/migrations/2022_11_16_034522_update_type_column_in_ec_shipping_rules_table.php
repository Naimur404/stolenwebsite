<?php

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_shipping_rules', function (Blueprint $table) {
            $table->string('type', 24)->default(ShippingRuleTypeEnum::BASED_ON_PRICE)->nullable()->change();
        });

        if (! Schema::hasColumn('ec_shipping_rule_items', 'zip_code')) {
            Schema::table('ec_shipping_rule_items', function (Blueprint $table) {
                $table->string('zip_code', 20)->nullable()->after('city');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ec_shipping_rules', function (Blueprint $table) {
            $table->string('type', 24)
                ->default(ShippingRuleTypeEnum::BASED_ON_PRICE)
                ->nullable()
                ->comment(implode(', ', [ShippingRuleTypeEnum::BASED_ON_PRICE, ShippingRuleTypeEnum::BASED_ON_WEIGHT]))
                ->change();
        });

        Schema::table('ec_shipping_rule_items', function (Blueprint $table) {
            $table->dropColumn('zip_code');
        });
    }
};
