<?php

namespace Botble\Ecommerce\Exports;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CsvProductExport implements FromCollection, WithHeadings
{
    use Exportable;

    protected Collection $results;

    protected bool $isMarketplaceActive;

    protected bool $enabledDigital;

    public function __construct()
    {
        $this->results = collect();

        $this->isMarketplaceActive = is_plugin_active('marketplace');
        $this->enabledDigital = EcommerceHelper::isEnabledSupportDigitalProducts();

        $with = [
            'categories',
            'slugable',
            'brand',
            'taxes',
            'productLabels',
            'productCollections',
            'variations',
            'variations.product',
            'variations.configurableProduct',
            'variations.productAttributes.productAttributeSet',
            'tags',
            'productAttributeSets',
        ];
        if ($this->isMarketplaceActive) {
            $with[] = 'store';
        }

        Product::query()
            ->select(['*'])
            ->where('is_variation', 0)
            ->with($with)
            ->chunk(400, function ($products) {
                $this->results = $this->results->concat($this->productResults($products));
            });
    }

    public function productResults(Collection $products): array
    {
        $results = [];
        foreach ($products as $product) {
            $productAttributes = [];
            if (! $product->is_variation) {
                $productAttributes = $product->productAttributeSets->pluck('title')->all();
            }

            $result = [
                'name' => $product->name,
                'description' => $product->description,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'categories' => implode(',', $product->categories->pluck('name')->all()),
                'status' => $product->status->getValue(),
                'is_featured' => $product->is_featured,
                'brand' => $product->brand->name,
                'product_collections' => implode(',', $product->productCollections->pluck('name')->all()),
                'labels' => implode(',', $product->productLabels->pluck('name')->all()),
                'taxes' => implode(',', $product->taxes->pluck('title')->all()),
                'images' => implode(',', $product->images),
                'price' => $product->price,
                'product_attributes' => implode(',', $productAttributes),
                'import_type' => 'product',
                'is_variation_default' => $product->is_variation_default,
                'stock_status' => $product->stock_status->getValue(),
                'with_storehouse_management' => $product->with_storehouse_management,
                'quantity' => $product->quantity,
                'allow_checkout_when_out_of_stock' => $product->allow_checkout_when_out_of_stock,
                'sale_price' => $product->sale_price,
                'start_date_sale_price' => $product->start_date,
                'end_date_sale_price' => $product->end_date,
                'weight' => $product->weight,
                'length' => $product->length,
                'wide' => $product->wide,
                'height' => $product->height,
                'cost_per_item' => $product->cost_per_item,
                'barcode' => $product->barcode,
                'content' => $product->content,
                'tags' => implode(',', $product->tags->pluck('name')->all()),
            ];

            if ($this->enabledDigital) {
                $result['product_type'] = $product->product_type;
            }

            if ($this->isMarketplaceActive) {
                $result['vendor'] = $product->store_id ? $product->store->name : null;
            }

            $results[] = $result;

            if ($product->variations->count()) {
                foreach ($product->variations as $variation) {
                    $productAttributes = $this->getProductAttributes($variation);

                    $results[] = array_merge(
                        [
                            'name' => $variation->product->name,
                            'description' => '',
                            'slug' => '',
                            'sku' => $variation->product->sku,
                            'categories' => '',
                            'status' => $variation->product->status->getValue(),
                            'is_featured' => '',
                            'brand' => '',
                            'product_collections' => '',
                            'labels' => '',
                            'taxes' => '',
                            'images' => implode(',', $variation->product->images),
                            'price' => $variation->product->price,
                            'product_attributes' => implode(',', $productAttributes),
                            'import_type' => 'variation',
                            'is_variation_default' => $variation->is_default,
                            'stock_status' => $variation->product->stock_status->getValue(),
                            'with_storehouse_management' => $variation->product->with_storehouse_management,
                            'quantity' => $variation->product->quantity,
                            'allow_checkout_when_out_of_stock' => $variation->product->allow_checkout_when_out_of_stock,
                            'sale_price' => $variation->product->sale_price,
                            'start_date_sale_price' => $variation->product->start_date,
                            'end_date_sale_price' => $variation->product->end_date,
                            'weight' => $variation->product->weight,
                            'length' => $variation->product->length,
                            'wide' => $variation->product->wide,
                            'height' => $variation->product->height,
                            'cost_per_item' => $variation->product->cost_per_item,
                            'barcode' => $variation->product->barcode,
                            'content' => '',
                            'tags' => '',
                        ],
                        $this->enabledDigital ? ['product_type' => ''] : [],
                        $this->isMarketplaceActive ? ['vendor' => ''] : []
                    );
                }
            }
        }

        return $results;
    }

    public function getProductAttributes(Product|ProductVariation $product): array
    {
        $productAttributes = [];
        foreach ($product->productAttributes as $productAttribute) {
            if ($productAttribute->productAttributeSet) {
                $productAttributes[] = $productAttribute->productAttributeSet->title . ':' . $productAttribute->title;
            }
        }

        return $productAttributes;
    }

    public function collection(): Collection
    {
        return $this->results;
    }

    public function headings(): array
    {
        $headings = [
            'name' => 'Product name',
            'description' => 'Description',
            'slug' => 'Slug',
            'sku' => 'SKU',
            'categories' => 'Categories',
            'status' => 'Status',
            'is_featured' => 'Is featured?',
            'brand' => 'Brand',
            'product_collections' => 'Product collections',
            'labels' => 'Labels',
            'taxes' => 'Taxes',
            'images' => 'Images',
            'price' => 'Price',
            'product_attributes' => 'Product attributes',
            'import_type' => 'Import type',
            'is_variation_default' => 'Is variation default?',
            'stock_status' => 'Stock status',
            'with_storehouse_management' => 'With storehouse management',
            'quantity' => 'Quantity',
            'allow_checkout_when_out_of_stock' => 'Allow checkout when out of stock',
            'sale_price' => 'Sale price',
            'start_date_sale_price' => 'Start date sale price',
            'end_date_sale_price' => 'End date sale price',
            'weight' => 'Weight',
            'length' => 'Length',
            'wide' => 'Wide',
            'height' => 'Height',
            'cost_per_item' => 'Cost per item',
            'barcode' => 'Barcode',
            'content' => 'Content',
            'tags' => 'Tags',
        ];

        if ($this->enabledDigital) {
            $headings['product_type'] = 'Product type';
        }

        if ($this->isMarketplaceActive) {
            $headings['vendor'] = 'Vendor';
        }

        return $headings;
    }
}
