<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\OptionValue;
use Botble\Ecommerce\Models\Product;
use Botble\Media\Models\MediaFile;
use Botble\Media\Services\UploadsManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StoreProductService
{
    public function execute(Request $request, Product $product, bool $forceUpdateAll = false): Product
    {
        $data = $request->input();

        $hasVariation = $product->variations()->count() > 0;

        if ($hasVariation && ! $forceUpdateAll) {
            $data = $request->except([
                'sku',
                'quantity',
                'allow_checkout_when_out_of_stock',
                'with_storehouse_management',
                'stock_status',
                'sale_type',
                'price',
                'sale_price',
                'start_date',
                'end_date',
                'length',
                'wide',
                'height',
                'weight',
                'generate_license_code',
            ]);
        }

        $product->fill($data);

        $images = [];

        if ($imagesInput = $request->input('images', [])) {
            $images = array_values(array_filter((array)$imagesInput));
        }

        $product->images = json_encode($images);

        if (! $hasVariation || $forceUpdateAll) {
            if ($product->sale_price > $product->price) {
                $product->sale_price = null;
            }

            if ($product->sale_type == 0) {
                $product->start_date = null;
                $product->end_date = null;
            }
        }

        $exists = $product->getKey();

        if (! $exists && EcommerceHelper::isEnabledCustomerRecentlyViewedProducts() && $request->input('product_type')) {
            if (in_array($request->input('product_type'), ProductTypeEnum::toArray())) {
                $product->product_type = $request->input('product_type');
            }
        }

        $product->save();

        if (! $exists) {
            event(new CreatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, $request, $product));
        } else {
            event(new UpdatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, $request, $product));
        }

        $product->categories()->sync($request->input('categories', []));

        $product->productCollections()->sync($request->input('product_collections', []));

        $product->productLabels()->sync($request->input('product_labels', []));

        $product->taxes()->sync($request->input('taxes', []));

        if ($request->has('related_products')) {
            $product->products()->detach();

            if ($relatedProducts = $request->input('related_products', '')) {
                $product->products()->attach(array_filter(explode(',', $relatedProducts)));
            }
        }

        if ($request->has('cross_sale_products')) {
            $product->crossSales()->detach();

            if ($crossSaleProducts = $request->input('cross_sale_products', '')) {
                $product->crossSales()->attach(array_filter(explode(',', $crossSaleProducts)));
            }
        }

        if ($request->has('up_sale_products')) {
            $product->upSales()->detach();

            if ($upSaleProducts = $request->input('up_sale_products', '')) {
                $product->upSales()->attach(array_filter(explode(',', $upSaleProducts)));
            }
        }

        if (EcommerceHelper::isEnabledSupportDigitalProducts() && $product->isTypeDigital()) {
            $this->saveProductFiles($request, $product);
        }

        if (EcommerceHelper::isEnabledProductOptions() && $request->input('has_product_options')) {
            $this->saveProductOptions((array)$request->input('options', []), $product);
        }

        event(new ProductQuantityUpdatedEvent($product));

        return $product;
    }

    public function saveProductFiles(Request $request, Product $product, bool $exists = true): Product
    {
        if ($exists) {
            foreach ($request->input('product_files', []) as $key => $value) {
                if (! $value) {
                    $product->productFiles()->where('id', $key)->delete();
                }
            }
        }

        if ($request->hasFile('product_files_input')) {
            foreach ($request->file('product_files_input', []) as $file) {
                try {
                    $data = $this->saveProductFile($file);
                    $product->productFiles()->create($data);
                } catch (Exception $ex) {
                    info($ex);
                }
            }
        }

        if ($filesExternal = (array) $request->input('product_files_external', [])) {
            foreach ($filesExternal as $fileExternal) {
                $size = Arr::get($fileExternal, 'size');
                if ($size) {
                    $unit = Arr::get($fileExternal, 'unit');
                    $size = match ($unit) {
                        'kB' => $size * 1024,
                        'MB' => $size * 1024 * 1024,
                        'GB' => $size * 1024 * 1024 * 1024,
                        'TB' => $size * 1024 * 1024 * 1024 * 1024,
                        default => $size
                    };
                }

                $product->productFiles()->create([
                    'url' => Arr::get($fileExternal, 'link'),
                    'extras' => [
                        'is_external' => true,
                        'name' => Arr::get($fileExternal, 'name'),
                        'size' => $size,
                    ],
                ]);
            }
        }

        return $product;
    }

    public function saveProductFile(UploadedFile $file): array
    {
        $folderPath = 'product-files';
        $fileExtension = $file->getClientOriginalExtension();
        $content = File::get($file->getRealPath());
        $name = File::name($file->getClientOriginalName());
        $fileName = MediaFile::createSlug(
            $name,
            $fileExtension,
            Storage::path($folderPath)
        );

        $filePath = $folderPath . '/' . $fileName;
        app(UploadsManager::class)->saveFile($filePath, $content, $file);
        $data = app(UploadsManager::class)->fileDetails($filePath);
        $data['name'] = $name;
        $data['extension'] = $fileExtension;

        return [
            'url' => $filePath,
            'extras' => $data,
        ];
    }

    protected function saveProductOptions(array $options, Product $product): void
    {
        $optionIds = [];

        try {
            foreach ($options as $opt) {
                $option = $product->options()->find($opt['id']);

                if (! $option) {
                    $option = new Option();
                }

                $opt['required'] = isset($opt['required']) && $opt['required'] === 'on';
                $option->fill($opt);
                $option->product_id = $product->getKey();
                $option->save();
                $option->values()->delete();

                if (! empty($opt['values'])) {
                    $optionValues = [];
                    foreach ($opt['values'] as $value) {
                        $optionValue = new OptionValue();
                        if (! isset($value['option_value'])) {
                            $value['option_value'] = '';
                        }
                        $optionValue->fill($value);
                        $optionValues[] = $optionValue;
                    }

                    $option->values()->saveMany($optionValues);
                }

                $optionIds[] = $option->id;
            }

            $product->options()->whereNotIn('id', $optionIds)->get()->each(function (Option $deletedOption) {
                $deletedOption->delete();
            });
        } catch (Exception $exception) {
            info($exception->getMessage());
        }
    }
}
