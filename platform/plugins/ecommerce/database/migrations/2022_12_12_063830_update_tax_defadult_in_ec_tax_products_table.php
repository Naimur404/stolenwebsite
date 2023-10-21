<?php

use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_tax_products')) {
            try {
                DB::table('ec_tax_products')->where('tax_id', 0)->delete();

                $defaultTaxRate = get_ecommerce_setting('default_tax_rate');

                if ($defaultTaxRate) {
                    foreach (Product::query()->where('is_variation', 0)->withCount(['taxes'])->get() as $product) {
                        $taxId = $product->tax_id ?: $defaultTaxRate;
                        if ($taxId && ! $product->taxes_count) {
                            DB::table('ec_tax_products')->insertOrIgnore([
                                'product_id' => $product->id,
                                'tax_id' => $taxId,
                            ]);
                        }
                    }
                }
            } catch (Throwable $exception) {
                info($exception->getMessage());
            }
        }
    }
};
