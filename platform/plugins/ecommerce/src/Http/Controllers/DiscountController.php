<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Http\Requests\DiscountRequest;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Tables\DiscountTable;
use Botble\Media\Facades\RvMedia;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DiscountController extends BaseController
{
    public function index(DiscountTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::discount.name'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);

        return $dataTable->renderTable();
    }

    public function create()
    {
        PageTitle::setTitle(trans('plugins/ecommerce::discount.create'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/discount.js',
            ])
            ->addScripts(['timepicker', 'input-mask', 'blockui'])
            ->addStyles(['timepicker']);

        Assets::usingVueJS();

        return view('plugins/ecommerce::discounts.create');
    }

    public function store(DiscountRequest $request, BaseHttpResponse $response)
    {
        $discount = Discount::query()->create($request->validated());

        if ($discount) {
            $productCollections = $request->input('product_collections');
            if ($productCollections) {
                if (! is_array($productCollections)) {
                    $productCollections = [$productCollections];
                    $discount->productCollections()->attach($productCollections);
                }
            }

            $products = $request->input('products');

            if ($products) {
                if (is_string($products) && Str::contains($products, ',')) {
                    $products = explode(',', $products);
                }

                if (! is_array($products)) {
                    $products = [$products];
                }

                foreach ($products as $productId) {
                    $product = Product::query()->find($productId);

                    if (! $product || $product->is_variation) {
                        Arr::forget($products, $productId);
                    }

                    $products = array_merge($products, $product->variations()->pluck('product_id')->all());
                }

                $discount->products()->attach(array_unique($products));
            }

            $variants = $request->input('variants');
            if ($variants) {
                if (is_string($variants) && Str::contains($variants, ',')) {
                    $variants = explode(',', $variants);
                }

                if (! is_array($variants)) {
                    $variants = [$variants];
                }

                foreach ($variants as $variantId) {
                    $product = Product::query()->find($variantId);

                    if (! $product || ! $product->is_variation || ! $product->original_product->id) {
                        Arr::forget($products, $product->getKey());
                    }

                    $variants = array_merge($variants, [$product->original_product->id]);
                }

                $discount->products()->attach(array_unique($variants));
            }

            $customers = $request->input('customers');
            if ($customers) {
                if (is_string($customers) && Str::contains($customers, ',')) {
                    $customers = explode(',', $customers);
                }

                if (! is_array($customers)) {
                    $customers = [$customers];
                }

                $discount->customers()->attach(array_unique($customers));
            }
        }

        event(new CreatedContentEvent(DISCOUNT_MODULE_SCREEN_NAME, $request, $discount));

        return $response
            ->setNextUrl(route('discounts.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id)
    {
        $discount = Discount::query()
            ->with([
                'customers',
                'productCollections',
                'products' => function (BelongsToMany $query) {
                    $query->where('is_variation', false);
                },
                'productVariants.variationInfo.variationItems.attribute',
            ])
            ->findOrFail($id);

        foreach ($discount->productVariants as $productVariant) {
            $productVariant->image_url = RvMedia::getImageUrl($productVariant->image, 'thumb', false, RvMedia::getDefaultImage());
            $productVariant->price = $productVariant->variationInfo->product->front_sale_price;
            foreach ($productVariant->variationInfo->variationItems as $variationItem) {
                $variationItem->attribute_title = $variationItem->attribute->title;
            }

            $productVariant->variationItems = $productVariant->variationInfo->variationItems;
        }

        $discount->products->each(function ($product) {
            $product->image_url = RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage());
        });

        $discount->customers->each(function ($customer) {
            $customer->avatar_url = RvMedia::getImageUrl($customer->avatar, 'thumb', false, RvMedia::getDefaultImage());
        });

        PageTitle::setTitle(trans('plugins/ecommerce::discount.edit'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/discount.js',
            ])
            ->addScripts(['timepicker', 'input-mask', 'blockui'])
            ->addStyles(['timepicker']);

        Assets::usingVueJS();

        return view('plugins/ecommerce::discounts.edit', compact('discount'));
    }

    public function update(int|string $id, DiscountRequest $request, BaseHttpResponse $response)
    {
        $discount = Discount::query()
            ->findOrFail($id);

        $discount->update($request->validated());

        if ($productCollections = $request->input('product_collections')) {
            if (! is_array($productCollections)) {
                $productCollections = [$productCollections];
                $discount->productCollections()->sync($productCollections);
            }
        }

        if ($products = $request->input('products')) {
            if (is_string($products) && Str::contains($products, ',')) {
                $products = explode(',', $products);
            }

            if (! is_array($products)) {
                $products = [$products];
            }

            foreach ($products as $productId) {
                $product = Product::query()->find($productId);

                if (! $product || $product->is_variation) {
                    Arr::forget($products, $productId);
                }

                $products = array_merge($products, $product->variations()->pluck('product_id')->all());
            }

            $discount->products()->sync(array_unique($products));
        } else {
            $discount->products()->detach();
        }

        if ($variants = $request->input('variants')) {
            if (is_string($variants) && Str::contains($variants, ',')) {
                $variants = explode(',', $variants);
            }

            if (! is_array($variants)) {
                $variants = [$variants];
            }

            foreach ($variants as $variantId) {
                $product = Product::query()->find($variantId);

                if (! $product || ! $product->is_variation || ! $product->original_product->id) {
                    Arr::forget($products, $product->id);
                }

                $variants = array_merge($variants, [$product->original_product->id]);
            }

            $discount->products()->sync(array_unique($variants));
        }

        if ($customers = $request->input('customers')) {
            if (is_string($customers) && Str::contains($customers, ',')) {
                $customers = explode(',', $customers);
            }

            if (! is_array($customers)) {
                $customers = [$customers];
            }

            $discount->customers()->sync(array_unique($customers));
        } else {
            $discount->customers()->detach();
        }

        event(new UpdatedContentEvent(DISCOUNT_MODULE_SCREEN_NAME, $request, $discount));

        return $response
            ->setNextUrl(route('discounts.edit', $discount))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $discount = Discount::query()->findOrFail($id);
            $discount->delete();

            event(new DeletedContentEvent(DISCOUNT_MODULE_SCREEN_NAME, $request, $discount));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function postGenerateCoupon(BaseHttpResponse $response)
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (Discount::query()->where(['code' => $code])->exists());

        return $response->setData($code);
    }
}
