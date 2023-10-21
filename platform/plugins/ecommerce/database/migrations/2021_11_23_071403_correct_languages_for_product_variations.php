<?php

use Botble\Ecommerce\Models\Product;
use Botble\Language\Facades\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        if (is_plugin_active('language') &&
            is_plugin_active('language-advanced')
        ) {
            $records = [];
            foreach (Product::get() as $product) {
                foreach (Language::getActiveLanguage(['lang_code', 'lang_is_default']) as $language) {
                    if ($language->lang_is_default) {
                        continue;
                    }

                    $condition = [
                        'lang_code' => $language->lang_code,
                        'ec_products_id' => $product->id,
                    ];

                    $existing = DB::table('ec_products_translations')->where($condition)->count();

                    if ($existing) {
                        continue;
                    }

                    $parentTranslation = DB::table('ec_products_translations')->where([
                        'lang_code' => $language->lang_code,
                        'ec_products_id' => $product->original_product->id,
                    ])->first();

                    $data = [];
                    foreach (DB::getSchemaBuilder()->getColumnListing('ec_products_translations') as $column) {
                        if (! in_array($column, array_keys($condition))) {
                            $data[$column] = $parentTranslation ? $parentTranslation->{$column} : $product->original_product->{$column};
                        }
                    }

                    $data = array_merge($data, $condition);

                    $records[] = $data;
                }
            }

            DB::table('ec_products_translations')->insertOrIgnore($records);
        }
    }
};
