<?php

namespace Botble\Ecommerce;

use Botble\Dashboard\Models\DashboardWidget;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Menu\Models\MenuNode;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Botble\Widget\Models\Widget;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function activated(): void
    {
        Setting::set([
            'payment_cod_status' => 1,
            'payment_bank_transfer_status' => 1,
        ])->save();

        app('migrator')->run(database_path('migrations'));
    }

    public static function remove(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ec_product_label_products');
        Schema::dropIfExists('ec_product_labels');
        Schema::dropIfExists('ec_product_tag_product');
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
        Schema::dropIfExists('ec_order_referrals');
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
        Schema::dropIfExists('ec_flash_sales');
        Schema::dropIfExists('ec_flash_sale_products');
        Schema::dropIfExists('ec_products_translations');
        Schema::dropIfExists('ec_product_categories_translations');
        Schema::dropIfExists('ec_product_attributes_translations');
        Schema::dropIfExists('ec_product_attribute_sets_translations');
        Schema::dropIfExists('ec_brands_translations');
        Schema::dropIfExists('ec_product_collections_translations');
        Schema::dropIfExists('ec_product_labels_translations');
        Schema::dropIfExists('ec_product_tags_translations');
        Schema::dropIfExists('ec_order_returns');
        Schema::dropIfExists('ec_order_return_items');
        Schema::dropIfExists('ec_global_options');
        Schema::dropIfExists('ec_global_option_value');
        Schema::dropIfExists('ec_global_options_translations');
        Schema::dropIfExists('ec_options_translations');
        Schema::dropIfExists('ec_option_value_translations');
        Schema::dropIfExists('ec_global_option_value_translations');
        Schema::dropIfExists('ec_options');
        Schema::dropIfExists('ec_option_value');
        Schema::dropIfExists('ec_invoice_items');
        Schema::dropIfExists('ec_invoices');
        Schema::dropIfExists('ec_tax_products');
        Schema::dropIfExists('ec_product_views');
        Schema::dropIfExists('ec_customer_used_coupons');
        Schema::dropIfExists('ec_order_tax_information');

        Widget::query()->where('name', 'widget_ecommerce_report_general')
            ->each(fn (DashboardWidget $dashboardWidget) => $dashboardWidget->delete());

        MenuNode::query()->whereIn('reference_type', [Brand::class, ProductCategory::class])
            ->each(fn (MenuNode $menuNode) => $menuNode->delete());
    }
}
