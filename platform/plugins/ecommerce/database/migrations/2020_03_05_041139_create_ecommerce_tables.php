<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $this->down();

        Schema::create('ec_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->mediumText('description')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('status', 60)->default('published');
            $table->tinyInteger('order')->unsigned()->default(0);
            $table->tinyInteger('is_featured')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::create('ec_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->default(0);
            $table->mediumText('description')->nullable();
            $table->string('status', 60)->default('published');
            $table->integer('order')->unsigned()->default(0);
            $table->string('image', 255)->nullable();
            $table->tinyInteger('is_featured')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::create('ec_product_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('description', 400)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('ec_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('symbol', 10);
            $table->tinyInteger('is_prefix_symbol')->unsigned()->default(0);
            $table->tinyInteger('decimals')->unsigned()->default(0)->nullable();
            $table->integer('order')->default(0)->unsigned()->nullable();
            $table->tinyInteger('is_default')->default(0);
            $table->double('exchange_rate')->default(1);
            $table->timestamps();
        });

        Schema::create('ec_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('status', 60)->default('published');
            $table->text('images')->nullable();
            $table->string('sku')->nullable();
            $table->integer('order')->unsigned()->default(0);
            $table->integer('quantity')->unsigned()->nullable();
            $table->tinyInteger('allow_checkout_when_out_of_stock')->unsigned()->default(0);
            $table->tinyInteger('with_storehouse_management')->unsigned()->default(0);
            $table->tinyInteger('is_featured')->unsigned()->default(0);
            $table->text('options')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->tinyInteger('is_variation')->default(0);
            $table->tinyInteger('is_searchable')->default(0);
            $table->tinyInteger('is_show_on_list')->default(0);
            $table->tinyInteger('sale_type')->default(0);
            $table->double('price')->unsigned()->nullable();
            $table->double('sale_price')->unsigned()->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->float('length')->nullable();
            $table->float('wide')->nullable();
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->string('barcode')->nullable();
            $table->string('length_unit', 20)->nullable();
            $table->string('wide_unit', 20)->nullable();
            $table->string('height_unit', 20)->nullable();
            $table->string('weight_unit', 20)->nullable();
            $table->foreignId('tax_id')->nullable();
            $table->bigInteger('views')->default(0);
            $table->timestamps();
        });

        Schema::create('ec_product_category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->index();
            $table->foreignId('product_id')->index();
            $table->primary(['product_id', 'category_id'], 'product_categories_product_primary_key');
        });

        Schema::create('ec_product_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('description', 400)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('ec_product_tag_product', function (Blueprint $table) {
            $table->foreignId('product_id')->index();
            $table->foreignId('tag_id')->index();

            $table->primary(['product_id', 'tag_id']);
        });

        Schema::create('ec_product_collection_products', function (Blueprint $table) {
            $table->foreignId('product_collection_id')->index();
            $table->foreignId('product_id')->index();
            $table->primary(['product_id', 'product_collection_id'], 'product_collections_product_primary_key');
        });

        Schema::create('ec_product_attribute_sets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('slug', 120)->nullable();
            $table->string('display_layout')->default('swatch_dropdown');
            $table->tinyInteger('is_searchable')->unsigned()->default(1);
            $table->tinyInteger('is_comparable')->unsigned()->default(1);
            $table->tinyInteger('is_use_in_product_listing')->unsigned()->default(0);
            $table->string('status', 60)->default('published');
            $table->tinyInteger('order')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::create('ec_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_set_id');
            $table->string('title', 120);
            $table->string('slug', 120)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('is_default')->unsigned()->default(0);
            $table->tinyInteger('order')->unsigned()->default(0);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('ec_product_with_attribute_set', function (Blueprint $table) {
            $table->foreignId('attribute_set_id');
            $table->foreignId('product_id');
            $table->tinyInteger('order')->unsigned()->default(0);

            $table->primary(['product_id', 'attribute_set_id'], 'product_with_attribute_set_primary_key');
        });

        Schema::create('ec_product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable();
            $table->foreignId('configurable_product_id');
            $table->tinyInteger('is_default')->default(0);
        });

        Schema::create('ec_product_variation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id');
            $table->foreignId('variation_id');
        });

        Schema::create('ec_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->float('percentage', 8, 6)->nullable();
            $table->integer('priority')->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('ec_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->foreignId('product_id');
            $table->float('star');
            $table->string('comment');
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('ec_shipping', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('country', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('ec_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('shipping_option', 60)->nullable();
            $table->string('shipping_method', 60)->default('default');
            $table->string('status', 120)->default('pending');
            $table->decimal('amount', 15);
            $table->foreignId('currency_id')->nullable();
            $table->decimal('tax_amount')->nullable();
            $table->decimal('shipping_amount')->nullable();
            $table->text('description')->nullable();
            $table->string('coupon_code', 120)->nullable();
            $table->decimal('discount_amount', 15)->nullable();
            $table->decimal('sub_total', 15);
            $table->boolean('is_confirmed')->default(false);
            $table->string('discount_description', 255)->nullable();
            $table->boolean('is_finished')->default(0)->nullable();
            $table->string('token', 120)->nullable();
            $table->foreignId('payment_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ec_order_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->integer('qty');
            $table->decimal('price', 15);
            $table->decimal('tax_amount', 15);
            $table->text('options')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->string('product_name');
            $table->float('weight')->default(0)->nullable();
            $table->integer('restock_quantity', false, true)->default(0)->nullable();
            $table->timestamps();
        });

        Schema::create('ec_order_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('country', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignId('order_id');
        });

        Schema::create('ec_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120)->nullable();
            $table->string('code', 20)->unique()->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('total_used')->unsigned()->default(0);
            $table->double('value')->nullable();
            $table->string('type', 60)->default('coupon')->nullable();
            $table->boolean('can_use_with_promotion')->default(false);
            $table->string('discount_on', 20)->nullable();
            $table->integer('product_quantity', false, true)->nullable();
            $table->string('type_option', 100)->default('amount');
            $table->string('target', 100)->default('all-orders');
            $table->decimal('min_order_price', 15)->nullable();
            $table->timestamps();
        });

        Schema::create('ec_wish_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->foreignId('product_id');
            $table->timestamps();
        });

        Schema::create('ec_cart', function (Blueprint $table) {
            $table->string('identifier', 60);
            $table->string('instance', 60);
            $table->longText('content');
            $table->nullableTimestamps();

            $table->primary(['identifier', 'instance'], 'ec_cart_primary');
        });

        Schema::create('ec_grouped_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_product_id');
            $table->foreignId('product_id');
            $table->integer('fixed_qty')->default(1);
        });

        Schema::create('ec_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar', 255)->nullable();
            $table->date('dob')->nullable();
            $table->string('phone', 25)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('ec_customer_password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('ec_customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email', 60)->nullable();
            $table->string('phone');
            $table->string('country', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('address');
            $table->foreignId('customer_id');
            $table->tinyInteger('is_default')->default(0)->unsigned();
            $table->timestamps();
        });

        Schema::create('ec_product_related_relations', function (Blueprint $table) {
            $table->foreignId('from_product_id')->index();
            $table->foreignId('to_product_id')->index();
            $table->primary(['from_product_id', 'to_product_id'], 'product_related_primary_key');
        });

        Schema::create('ec_product_cross_sale_relations', function (Blueprint $table) {
            $table->foreignId('from_product_id')->index();
            $table->foreignId('to_product_id')->index();

            $table->primary(['from_product_id', 'to_product_id'], 'product_cross_sale_primary_key');
        });

        Schema::create('ec_product_up_sale_relations', function (Blueprint $table) {
            $table->foreignId('from_product_id')->index();
            $table->foreignId('to_product_id')->index();
            $table->primary(['from_product_id', 'to_product_id'], 'product_up_sale_primary_key');
        });

        Schema::create('ec_shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->foreignId('shipping_id');
            $table->string('type', 60)->default('based_on_price')->nullable();
            $table->foreignId('currency_id')->nullable();
            $table->decimal('from', 15)->default(0)->nullable();
            $table->decimal('to', 15)->default(0)->nullable();
            $table->decimal('price', 15)->default(0)->nullable();
            $table->timestamps();
        });

        Schema::create('ec_shipping_rule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_rule_id');
            $table->string('country', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->decimal('adjustment_price', 15)->default(0)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('ec_order_histories', function (Blueprint $table) {
            $table->id();
            $table->string('action', 120);
            $table->string('description', 255);
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_id');
            $table->text('extras')->nullable();
            $table->timestamps();
        });

        Schema::create('ec_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('user_id')->nullable();
            $table->float('weight')->default(0)->nullable();
            $table->string('shipment_id', 120)->nullable();
            $table->string('note', 120)->nullable();
            $table->string('status', 120)->default('pending');
            $table->decimal('cod_amount', 15)->default(0)->nullable();
            $table->string('cod_status', 60)->default('pending');
            $table->string('cross_checking_status', 60)->default('pending');
            $table->decimal('price', 15)->default(0)->nullable();
            $table->foreignId('store_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ec_store_locators', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('email', 60)->nullable();
            $table->string('phone', 20);
            $table->string('address', 255);
            $table->string('country', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->boolean('is_primary')->default(false)->nullable();
            $table->boolean('is_shipping_location')->default(true)->nullable();
            $table->timestamps();
        });

        Schema::create('ec_shipment_histories', function (Blueprint $table) {
            $table->id();
            $table->string('action', 120);
            $table->string('description', 255);
            $table->foreignId('user_id')->nullable();
            $table->foreignId('shipment_id');
            $table->foreignId('order_id');
            $table->timestamps();
        });

        Schema::create('ec_discount_products', function (Blueprint $table) {
            $table->foreignId('discount_id');
            $table->foreignId('product_id');
            $table->primary(['discount_id', 'product_id']);
        });

        Schema::create('ec_discount_customers', function (Blueprint $table) {
            $table->foreignId('discount_id');
            $table->foreignId('customer_id');
            $table->primary(['discount_id', 'customer_id']);
        });

        Schema::create('ec_discount_product_collections', function (Blueprint $table) {
            $table->foreignId('discount_id');
            $table->foreignId('product_collection_id');
            $table->primary(['discount_id', 'product_collection_id'], 'discount_product_collections_primary_key');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ec_product_collection_products');
        Schema::dropIfExists('ec_product_category_product');
        Schema::dropIfExists('ec_prices');
        Schema::dropIfExists('ec_products');
        Schema::dropIfExists('ec_currencies');
        Schema::dropIfExists('ec_product_collections');
        Schema::dropIfExists('ec_product_categories');
        Schema::dropIfExists('ec_product_tag_product');
        Schema::dropIfExists('ec_product_tags');
        Schema::dropIfExists('ec_brands');
        Schema::dropIfExists('ec_product_variation_items');
        Schema::dropIfExists('ec_product_variations');
        Schema::dropIfExists('ec_product_with_attribute_set');
        Schema::dropIfExists('ec_product_attributes');
        Schema::dropIfExists('ec_product_attribute_sets');
        Schema::dropIfExists('ec_taxes');
        Schema::dropIfExists('ec_reviews');
        Schema::dropIfExists('ec_shipping');
        Schema::dropIfExists('ec_orders');
        Schema::dropIfExists('ec_order_product');
        Schema::dropIfExists('ec_order_addresses');
        Schema::dropIfExists('ec_discounts');
        Schema::dropIfExists('ec_wish_lists');
        Schema::dropIfExists('ec_cart');
        Schema::dropIfExists('ec_grouped_products');
        Schema::dropIfExists('ec_customers');
        Schema::dropIfExists('ec_customer_password_resets');
        Schema::dropIfExists('ec_customer_addresses');
        Schema::dropIfExists('ec_product_up_sale_relations');
        Schema::dropIfExists('ec_product_cross_sale_relations');
        Schema::dropIfExists('ec_product_related_relations');
        Schema::dropIfExists('ec_shipping_rules');
        Schema::dropIfExists('ec_shipping_rule_items');
        Schema::dropIfExists('ec_order_histories');
        Schema::dropIfExists('ec_shipments');
        Schema::dropIfExists('ec_shipment_histories');
        Schema::dropIfExists('ec_store_locators');
        Schema::dropIfExists('ec_discount_products');
        Schema::dropIfExists('ec_discount_customers');
        Schema::dropIfExists('ec_discount_product_collections');
    }
};
