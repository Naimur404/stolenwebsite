<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Helper;
use Botble\Ecommerce\Events\ProductViewed;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Resources\ProductVariationResource;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\ProductVariationItem;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Services\Products\GetProductService;
use Botble\Ecommerce\Services\Products\UpdateDefaultProductService;
use Botble\Media\Facades\RvMedia;
use Botble\SeoHelper\Entities\Twitter\Card;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\SeoHelper\SeoOpenGraph;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PublicProductController
{
    public function getProducts(Request $request, GetProductService $productService, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::productFilterParamsValidated($request)) {
            return $response->setNextUrl(route('public.products'));
        }

        $query = BaseHelper::stringify($request->input('q'));

        $with = EcommerceHelper::withProductEagerLoadingRelations();

        if ($query && ! $request->ajax()) {
            $products = $productService->getProduct($request, null, null, $with);

            SeoHelper::setTitle(__('Search result for ":query"', compact('query')));

            Theme::breadcrumb()
                ->add(__('Home'), route('public.index'))
                ->add(__('Search'), route('public.products'));

            SeoHelper::meta()->setUrl(route('public.products'));

            return Theme::scope(
                'ecommerce.search',
                compact('products', 'query'),
                'plugins/ecommerce::themes.search'
            )->render();
        }

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Products'), route('public.products'));

        $products = $productService->getProduct($request, null, null, $with);

        if ($request->ajax()) {
            return $this->ajaxFilterProductsResponse($products, $response);
        }

        SeoHelper::setTitle(__('Products'))->setDescription(__('Products'));

        do_action(PRODUCT_MODULE_SCREEN_NAME);

        return Theme::scope(
            'ecommerce.products',
            compact('products'),
            'plugins/ecommerce::themes.products'
        )->render();
    }

    public function getProduct(string $key, Request $request)
    {
        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(Product::class));

        if (! $slug) {
            abort(404);
        }

        $condition = [
            'ec_products.id' => $slug->reference_id,
        ];

        if (Auth::check() && $request->input('preview')) {
            Arr::forget($condition, 'ec_products.status');
        }

        $product = get_products(
            array_merge([
                'condition' => $condition,
                'take' => 1,
                'with' => [
                    'slugable',
                    'tags',
                    'tags.slugable',
                    'categories',
                    'categories.slugable',
                    'options',
                    'options.values',
                ],
            ], EcommerceHelper::withReviewsParams())
        );

        if (! $product) {
            abort(404);
        }

        if ($product->slugable->key !== $slug->key) {
            return redirect()->to($product->url);
        }

        SeoHelper::setTitle($product->name)->setDescription($product->description);

        $meta = new SeoOpenGraph();
        if ($product->image) {
            $meta->setImage(RvMedia::getImageUrl($product->image));
        }
        $meta->setDescription($product->description);
        $meta->setUrl($product->url);
        $meta->setTitle($product->name);

        SeoHelper::setSeoOpenGraph($meta);

        SeoHelper::meta()->setUrl($product->url);

        $card = new Card();
        $card->setType(Card::TYPE_PRODUCT);
        $card->addMeta('label1', 'Price');
        $card->addMeta(
            'data1',
            format_price($product->front_sale_price_with_taxes) . ' ' . strtoupper(get_application_currency()->title)
        );
        $card->addMeta('label2', 'Website');
        $card->addMeta('data2', SeoHelper::openGraph()->getProperty('site_name'));
        $card->addMeta('domain', url(''));

        SeoHelper::twitter()->setCard($card);

        if (Helper::handleViewCount($product, 'viewed_product')) {
            event(new ProductViewed($product, Carbon::now()));

            EcommerceHelper::handleCustomerRecentlyViewedProduct($product);
        }

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Products'), route('public.products'));

        $category = $product->categories->sortByDesc('id')->first();

        if ($category) {
            if ($category->parents->count()) {
                foreach ($category->parents->reverse() as $parentCategory) {
                    Theme::breadcrumb()->add($parentCategory->name, $parentCategory->url);
                }
            }

            Theme::breadcrumb()->add($category->name, $category->url);
        }

        Theme::breadcrumb()->add($product->name, $product->url);

        if (function_exists('admin_bar')) {
            admin_bar()
                ->registerLink(
                    trans('plugins/ecommerce::products.edit_this_product'),
                    route('products.edit', $product->id),
                    null,
                    'products.edit'
                );
        }

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, PRODUCT_MODULE_SCREEN_NAME, $product);

        [$productImages, $productVariation, $selectedAttrs] = EcommerceHelper::getProductVariationInfo(
            $product,
            $request->input()
        );

        if (! $product->is_variation && $productVariation) {
            $product = app(UpdateDefaultProductService::class)->updateColumns($product, $productVariation);
        }

        return Theme::scope(
            'ecommerce.product',
            compact('product', 'selectedAttrs', 'productImages', 'productVariation'),
            'plugins/ecommerce::themes.product'
        )
            ->render();
    }

    public function getProductTag(
        string $key,
        Request $request,
        GetProductService $getProductService,
        BaseHttpResponse $response
    ) {
        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(ProductTag::class));

        if (! $slug) {
            abort(404);
        }

        $condition = [
            'ec_product_categories.id' => $slug->reference_id,
            'ec_product_categories.status' => BaseStatusEnum::PUBLISHED,
        ];

        if (Auth::check() && $request->input('preview')) {
            Arr::forget($condition, 'ec_product_categories.status');
        }

        $tag = ProductTag::query()->with(['slugable', 'products'])->find($slug->reference_id);

        if (! $tag) {
            abort(404);
        }

        if ($tag->slugable->key !== $slug->key) {
            return redirect()->to($tag->url);
        }

        if (! EcommerceHelper::productFilterParamsValidated($request)) {
            return $response->setNextUrl($tag->url);
        }

        $with = EcommerceHelper::withProductEagerLoadingRelations();

        $request->merge([
            'tags' => [$tag->id],
        ]);

        $products = $getProductService->getProduct($request, null, null, $with);

        if ($request->ajax()) {
            return $this->ajaxFilterProductsResponse($products, $response);
        }

        SeoHelper::setTitle($tag->name)->setDescription($tag->description);

        $meta = new SeoOpenGraph();
        $meta->setDescription($tag->description);
        $meta->setUrl($tag->url);
        $meta->setTitle($tag->name);

        SeoHelper::setSeoOpenGraph($meta);

        SeoHelper::meta()->setUrl($tag->url);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Products'), route('public.products'))
            ->add($tag->name, $tag->url);

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, PRODUCT_TAG_MODULE_SCREEN_NAME, $tag);

        return Theme::scope(
            'ecommerce.product-tag',
            compact('tag', 'products'),
            'plugins/ecommerce::themes.product-tag'
        )->render();
    }

    public function getProductCategory(
        string $key,
        Request $request,
        GetProductService $getProductService,
        BaseHttpResponse $response
    ) {
        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(ProductCategory::class));

        if (! $slug) {
            abort(404);
        }

        $condition = [
            'ec_product_categories.id' => $slug->reference_id,
            'ec_product_categories.status' => BaseStatusEnum::PUBLISHED,
        ];

        if (Auth::check() && $request->input('preview')) {
            Arr::forget($condition, 'ec_product_categories.status');
        }

        $category = ProductCategory::query()->with(['slugable'])->where($condition)->first();

        if (! $category) {
            abort(404);
        }

        if ($category->slugable->key !== $slug->key) {
            return redirect()->to($category->url);
        }

        if (! EcommerceHelper::productFilterParamsValidated($request)) {
            return $response->setNextUrl($category->url);
        }

        $with = EcommerceHelper::withProductEagerLoadingRelations();

        $request->merge([
            'categories' => array_merge(
                [$category->id],
                $category->activeChildren->pluck('id')->all()
            ),
        ]);

        $products = $getProductService->getProduct($request, null, null, $with);

        $request->merge([
            'categories' => array_merge(
                $category->parents->pluck('id')->all(),
                $request->input('categories')
            ),
        ]);

        SeoHelper::setTitle($category->name)->setDescription($category->description);

        $meta = new SeoOpenGraph();
        if ($category->image) {
            $meta->setImage(RvMedia::getImageUrl($category->image));
        }
        $meta->setDescription($category->description);
        $meta->setUrl($category->url);
        $meta->setTitle($category->name);

        SeoHelper::setSeoOpenGraph($meta);

        SeoHelper::meta()->setUrl($category->url);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Products'), route('public.products'));

        if ($category->parents->count()) {
            foreach ($category->parents->reverse() as $parentCategory) {
                Theme::breadcrumb()->add($parentCategory->name, $parentCategory->url);
            }
        }

        Theme::breadcrumb()->add($category->name, $category->url);

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, PRODUCT_CATEGORY_MODULE_SCREEN_NAME, $category);

        if ($request->ajax()) {
            return $this->ajaxFilterProductsResponse($products, $response, $category);
        }

        return Theme::scope(
            'ecommerce.product-category',
            compact('category', 'products'),
            'plugins/ecommerce::themes.product-category'
        )->render();
    }

    public function getProductVariation(
        int|string $id,
        Request $request,
        BaseHttpResponse $response,
        ProductInterface $productRepository
    ) {
        $product = null;

        if ($attributes = $request->input('attributes', [])) {
            $variation = ProductVariation::getVariationByAttributes($id, $attributes);

            if ($variation) {
                $product = $productRepository->getProductVariations($id, [
                    'condition' => [
                        'ec_product_variations.id' => $variation->getKey(),
                        'original_products.status' => BaseStatusEnum::PUBLISHED,
                    ],
                    'select' => [
                        'ec_products.id',
                        'ec_products.name',
                        'ec_products.quantity',
                        'ec_products.price',
                        'ec_products.sale_price',
                        'ec_products.allow_checkout_when_out_of_stock',
                        'ec_products.with_storehouse_management',
                        'ec_products.stock_status',
                        'ec_products.images',
                        'ec_products.sku',
                        'ec_products.description',
                        'ec_products.is_variation',
                        'original_products.images as original_images',
                        'ec_products.height',
                        'ec_products.weight',
                        'ec_products.wide',
                        'ec_products.length',
                    ],
                    'take' => 1,
                ]);
            }
        } else {
            $product = Product::query()
                ->where('id', $id)
                ->wherePublished()
                ->select([
                    'id',
                    'name',
                    'quantity',
                    'price',
                    'sale_price',
                    'allow_checkout_when_out_of_stock',
                    'with_storehouse_management',
                    'stock_status',
                    'images',
                    'sku',
                    'description',
                    'is_variation',
                    'height',
                    'weight',
                    'wide',
                    'length',
                ])
                ->first();

            $attributes = $product ? $product->defaultVariation->productAttributes->pluck('id')->all() : [];
        }

        if ($product) {
            if ($product->images) {
                $originalImages = $product->images;

                if (get_ecommerce_setting(
                    'how_to_display_product_variation_images'
                ) == 'variation_images_and_main_product_images') {
                    $originalImages = array_merge(
                        $originalImages,
                        is_array($product->original_images) ? $product->original_images : json_decode(
                            $product->original_images,
                            true
                        )
                    );
                }
            } else {
                $originalImages = $product->original_images ?: $product->original_product->images;

                if (! is_array($originalImages)) {
                    $originalImages = json_decode($originalImages, true);
                }
            }

            $product->image_with_sizes = rv_get_image_list($originalImages, [
                'origin',
                'thumb',
            ]);

            if ($product->isOutOfStock()) {
                $product->errorMessage = __('Out of stock');
            }

            if (! $product->with_storehouse_management || $product->quantity < 1) {
                $product->successMessage = __('In stock');
            } elseif ($product->quantity) {
                if (EcommerceHelper::showNumberOfProductsInProductSingle()) {
                    if ($product->quantity != 1) {
                        $product->successMessage = __(':number products available', ['number' => $product->quantity]);
                    } else {
                        $product->successMessage = __(':number product available', ['number' => $product->quantity]);
                    }
                } else {
                    $product->successMessage = __('In stock');
                }
            }

            $originalProduct = $product->original_product;
        } else {
            $originalProduct = Product::query()
                ->where('id', $id)
                ->wherePublished()
                ->select([
                    'id',
                    'name',
                    'quantity',
                    'price',
                    'sale_price',
                    'allow_checkout_when_out_of_stock',
                    'with_storehouse_management',
                    'stock_status',
                    'images',
                    'sku',
                    'description',
                    'is_variation',
                    'height',
                    'weight',
                    'wide',
                    'length',
                ])
                ->first();

            if ($originalProduct) {
                if ($originalProduct->images) {
                    $originalProduct->image_with_sizes = rv_get_image_list($originalProduct->images, [
                        'origin',
                        'thumb',
                    ]);
                }

                $originalProduct->errorMessage = __('Please select attributes');
            }
        }

        if (! $originalProduct) {
            return $response->setError()->setMessage(__('Not available'));
        }

        $productAttributes = $productRepository->getRelatedProductAttributes($originalProduct)->sortBy('order');

        $attributeSets = $originalProduct->productAttributeSets()->orderBy('order')->get();

        $productVariations = ProductVariation::query()
            ->where('configurable_product_id', $originalProduct->id)
            ->get();

        $productVariationsInfo = ProductVariationItem::getVariationsInfo($productVariations->pluck('id')->toArray());

        $variationInfo = $productVariationsInfo;

        $unavailableAttributeIds = [];
        $variationNextIds = [];
        foreach ($attributeSets as $key => $set) {
            if ($key != 0) {
                $variationInfo = $productVariationsInfo
                    ->where('attribute_set_id', $set->id)
                    ->whereIn('variation_id', $variationNextIds);
            }

            [$variationNextIds, $unavailableAttributeIds] = handle_next_attributes_in_product(
                $productAttributes->where('attribute_set_id', $set->id),
                $productVariationsInfo,
                $set->id,
                $attributes,
                $key,
                $variationNextIds,
                $variationInfo,
                $unavailableAttributeIds
            );
        }

        if (! $product) {
            $product = $originalProduct;
        }

        if (! $product->is_variation) {
            $selectedAttributes = $product->defaultVariation->productAttributes->map(function ($item) {
                $item->attribute_set_slug = $item->productAttributeSet->slug;

                return $item;
            });
        } else {
            $selectedAttributes = $product->variationProductAttributes;
        }

        $product->unavailableAttributeIds = $unavailableAttributeIds;
        $product->selectedAttributes = $selectedAttributes;

        return $response
            ->setData(new ProductVariationResource($product));
    }

    public function getBrand(
        string $key,
        Request $request,
        GetProductService $getProductService,
        BaseHttpResponse $response
    ) {
        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(Brand::class));

        if (! $slug) {
            abort(404);
        }

        $brand = Brand::query()->where('id', $slug->reference_id)->with(['slugable'])->firstOrFail();

        if (! $brand) {
            abort(404);
        }

        if ($brand->slugable->key !== $slug->key) {
            return redirect()->to($brand->url);
        }

        if (! EcommerceHelper::productFilterParamsValidated($request)) {
            return $response->setNextUrl($brand->url);
        }

        $request->merge(['brands' => array_merge((array) request()->input('brands', []), [$brand->getKey()])]);

        $products = $getProductService->getProduct(
            $request,
            null,
            $brand->getKey(),
            EcommerceHelper::withProductEagerLoadingRelations()
        );

        if ($request->ajax()) {
            return $this->ajaxFilterProductsResponse($products, $response);
        }

        SeoHelper::setTitle($brand->name)->setDescription($brand->description);

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add($brand->name, $brand->url);

        $meta = new SeoOpenGraph();
        if ($brand->logo) {
            $meta->setImage(RvMedia::getImageUrl($brand->logo));
        }
        $meta->setDescription($brand->description);
        $meta->setUrl($brand->url);
        $meta->setTitle($brand->name);

        SeoHelper::setSeoOpenGraph($meta);

        SeoHelper::meta()->setUrl($brand->url);

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, BRAND_MODULE_SCREEN_NAME, $brand);

        return Theme::scope('ecommerce.brand', compact('brand', 'products'), 'plugins/ecommerce::themes.brand')
            ->render();
    }

    protected function ajaxFilterProductsResponse(
        $products,
        BaseHttpResponse $response,
        ?ProductCategory $category = null
    ) {
        $total = $products->total();
        $message = $total > 1 ? __(':total Products found', compact('total')) : __(
            ':total Product found',
            compact('total')
        );

        $productsView = Theme::getThemeNamespace('views.ecommerce.includes.product-items');

        if (! view()->exists($productsView)) {
            $productsView = 'plugins/ecommerce::themes.includes.product-items';
        }

        $data = view($productsView, compact('products'))->render();

        $breadcrumbView = Theme::getThemeNamespace('partials.breadcrumbs');

        if (view()->exists($breadcrumbView)) {
            $additional['breadcrumb'] = Theme::partial('breadcrumbs');
        } else {
            $additional['breadcrumb'] = Theme::breadcrumb()->render();
        }

        $filtersView = Theme::getThemeNamespace('views.ecommerce.includes.filters');

        if (view()->exists($filtersView)) {
            $additional['filters_html'] = view($filtersView, compact('category'))->render();
        }

        return $response
            ->setData($data)
            ->setAdditional($additional)
            ->setMessage($message);
    }

    public function getOrderTracking(Request $request)
    {
        if (! EcommerceHelper::isOrderTrackingEnabled()) {
            abort(404);
        }

        $order = null;

        $validator = Validator::make($request->only(['order_id', 'email']), [
            'order_id' => 'nullable|integer|min:1',
            'email' => 'nullable|email',
        ]);

        $title = __('Order tracking');

        if (! $validator->failed()) {
            $code = $request->input('order_id');
            $email = $request->input('email');

            $order = Order::query()
                ->where(function (Builder $query) use ($code) {
                    $query
                        ->where('ec_orders.code', $code)
                        ->orWhere('ec_orders.code', '#' . $code);
                })
                ->where(function (Builder $query) use ($email) {
                    $query
                        ->whereHas('address', function ($subQuery) use ($email) {
                            return $subQuery->where('email', $email);
                        })
                        ->orWhereHas('user', function ($subQuery) use ($email) {
                            return $subQuery->where('email', $email);
                        });
                })
                ->with(['address', 'payment', 'products'])
                ->select('ec_orders.*')
                ->first();

            $title = __('Order tracking :code', ['code' => $code]);
        }

        SeoHelper::setTitle($title);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add($title, route('public.orders.tracking'));

        return Theme::scope('ecommerce.order-tracking', compact('order'), 'plugins/ecommerce::themes.order-tracking')
            ->render();
    }
}
