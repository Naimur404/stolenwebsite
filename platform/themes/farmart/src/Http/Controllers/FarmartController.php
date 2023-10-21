<?php

namespace Theme\Farmart\Http\Controllers;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\Wishlist as WishlistModel;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Services\Products\GetProductService;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Http\Controllers\PublicController;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Theme\Farmart\Http\Requests\ContactSellerRequest;
use Theme\Farmart\Supports\Wishlist;

class FarmartController extends PublicController
{
    public function __construct(protected BaseHttpResponse $httpResponse)
    {
        $this->middleware(function ($request, $next) {
            if (! $request->ajax()) {
                return $this->httpResponse->setNextUrl(route('public.index'));
            }

            return $next($request);
        })->only([
            'ajaxCart',
            'ajaxGetQuickView',
            'ajaxAddProductToWishlist',
            'ajaxSearchProducts',
            'ajaxGetProductReviews',
            'ajaxGetRecentlyViewedProducts',
            'ajaxContactSeller',
            'ajaxGetProductsByCollection',
            'ajaxGetProductsByCategory',
        ]);
    }

    public function ajaxCart()
    {
        return $this->httpResponse->setData([
            'count' => Cart::instance('cart')->count(),
            'total_price' => format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()),
            'html' => Theme::partial('cart-mini.list'),
        ]);
    }

    public function ajaxGetQuickView(Request $request, int|string|null $id = null)
    {
        if (! $id) {
            $id = $request->integer('product_id');
        }

        $product = null;

        if ($id) {
            $product = get_products([
                    'condition' => [
                        'ec_products.id' => $id,
                    ],
                    'take' => 1,
                    'with' => [
                        'slugable',
                        'tags',
                        'tags.slugable',
                        'options',
                        'options.values',
                    ],
                ] + EcommerceHelper::withReviewsParams());
        }

        if (! $product) {
            return $this->httpResponse->setError()->setMessage(__('This product is not available.'));
        }

        [$productImages, $productVariation, $selectedAttrs] = EcommerceHelper::getProductVariationInfo($product);

        $wishlistIds = Wishlist::getWishlistIds([$product->getKey()]);

        return $this
            ->httpResponse
            ->setData(Theme::partial('ecommerce.quick-view', compact('product', 'selectedAttrs', 'productImages', 'productVariation', 'wishlistIds')));
    }

    public function ajaxAddProductToWishlist(Request $request, $productId = null)
    {
        if (! EcommerceHelper::isWishlistEnabled()) {
            abort(404);
        }
        if (! $productId) {
            $productId = $request->input('product_id');
        }

        if (! $productId) {
            return $this->httpResponse->setError()->setMessage(__('This product is not available.'));
        }

        $product = Product::query()->findOrFail($productId);

        $messageAdded = __('Added product :product successfully!', ['product' => $product->name]);
        $messageRemoved = __('Removed product :product from wishlist successfully!', ['product' => $product->name]);

        if (! auth('customer')->check()) {
            $duplicates = Cart::instance('wishlist')->search(function ($cartItem) use ($productId) {
                return $cartItem->id == $productId;
            });

            if (! $duplicates->isEmpty()) {
                $added = false;
                Cart::instance('wishlist')->search(function ($cartItem, $rowId) use ($productId) {
                    if ($cartItem->id == $productId) {
                        Cart::instance('wishlist')->remove($rowId);

                        return true;
                    }

                    return false;
                });
            } else {
                $added = true;
                Cart::instance('wishlist')
                    ->add($productId, $product->name, 1, $product->front_sale_price)
                    ->associate(Product::class);
            }

            return $this->httpResponse
                ->setMessage($added ? $messageAdded : $messageRemoved)
                ->setData([
                    'count' => Cart::instance('wishlist')->count(),
                    'added' => $added,
                ]);
        }

        $customer = auth('customer')->user();

        if (is_added_to_wishlist($productId)) {
            $added = false;
            WishlistModel::query()->where([
                'product_id' => $productId,
                'customer_id' => $customer->getKey(),
            ])->delete();
        } else {
            $added = true;
            WishlistModel::query()->create([
                'product_id' => $productId,
                'customer_id' => $customer->getKey(),
            ]);
        }

        return $this->httpResponse
            ->setMessage($added ? $messageAdded : $messageRemoved)
            ->setData([
                'count' => $customer->wishlist()->count(),
                'added' => $added,
            ]);
    }

    public function ajaxSearchProducts(Request $request, GetProductService $productService)
    {
        $request->merge(['num' => 12]);

        $with = EcommerceHelper::withProductEagerLoadingRelations();

        $products = $productService->getProduct($request, null, null, $with);

        $queries = $request->input();
        foreach ($queries as $key => $query) {
            if (! $query || $key == 'num' || (is_array($query) && ! Arr::get($query, 0))) {
                unset($queries[$key]);
            }
        }

        $total = $products->count();
        $message = $total != 1 ? __(':total Products found', compact('total')) : __(':total Product found', compact('total'));

        return $this->httpResponse
            ->setData(Theme::partial('ajax-search-results', compact('products', 'queries')))
            ->setMessage($message);
    }

    public function ajaxGetProductReviews(int|string $id, Request $request)
    {
        $product = Product::query()
            ->wherePublished()
            ->where([
                'id' => $id,
                'is_variation' => false,
            ])
            ->with(['variations'])
            ->firstOrFail();

        $star = $request->integer('star');
        $perPage = $request->integer('per_page', 10) ?: 10;

        $reviews = EcommerceHelper::getProductReviews($product, $star, $perPage);

        if ($star) {
            $message = __(':total review(s) ":star star" for ":product"', [
                'total' => $reviews->total(),
                'product' => $product->name,
                'star' => $star,
            ]);
        } else {
            $message = __(':total review(s) for ":product"', [
                'total' => $reviews->total(),
                'product' => $product->name,
            ]);
        }

        return $this->httpResponse
            ->setData(view(Theme::getThemeNamespace('views.ecommerce.includes.review-list'), compact('reviews'))->render())
            ->setMessage($message)
            ->toApiResponse();
    }

    public function ajaxGetRecentlyViewedProducts(ProductInterface $productRepository)
    {
        if (! EcommerceHelper::isEnabledCustomerRecentlyViewedProducts()) {
            abort(404);
        }

        $queryParams = [
                'with' => ['slugable'],
                'take' => 12,
            ] + EcommerceHelper::withReviewsParams();

        if (auth('customer')->check()) {
            $products = $productRepository->getProductsRecentlyViewed(auth('customer')->id(), $queryParams);
        } else {
            $products = collect();

            $itemIds = collect(Cart::instance('recently_viewed')->content())
                ->sortBy([['updated_at', 'desc']])
                ->take(12)
                ->pluck('id')
                ->all();

            if ($itemIds) {
                $products = $productRepository->getProductsByIds($itemIds, $queryParams);
            }
        }

        return $this->httpResponse
            ->setData(Theme::partial('ecommerce.recently-viewed-products', compact('products')));
    }

    public function ajaxContactSeller(ContactSellerRequest $request, BaseHttpResponse $response)
    {
        $name = $request->input('name');
        $email = $request->input('email');

        if (auth('customer')->check() && $user = auth('customer')->user()) {
            $name = $user->name;
            $email = $user->email;
        }

        EmailHandler::setModule(Theme::getThemeName())
            ->setVariableValues([
                'contact_message' => $request->input('content'),
                'customer_name' => $name,
                'customer_email' => $email,
            ])
            ->sendUsingTemplate('contact-seller', $email, [], false, 'themes');

        return $response->setMessage(__('Send message successfully!'));
    }

    public function ajaxGetProductsByCollection(int|string $id, Request $request, BaseHttpResponse $response)
    {
        if (! $request->expectsJson()) {
            return $response->setNextUrl(route('public.index'));
        }

        $products = get_products_by_collections(array_merge([
            'collections' => [
                'by' => 'id',
                'value_in' => [$id],
            ],
            'take' => $request->integer('limit') ?: 8,
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
        ], EcommerceHelper::withReviewsParams()));

        $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

        $data = [];
        foreach ($products as $product) {
            $data[] = '<div class="product-inner">' . Theme::partial('ecommerce.product-item', compact('product', 'wishlistIds')) . '</div>';
        }

        return $response->setData($data);
    }

    public function ajaxGetProductsByCategory(
        int|string $id,
        Request $request,
        BaseHttpResponse $response,
        ProductInterface $productRepository
    ) {
        if (! $request->expectsJson()) {
            return $response->setNextUrl(route('public.index'));
        }

        $category = ProductCategory::query()
            ->where('id', $id)
            ->wherePublished()
            ->with([
                'activeChildren' => function (HasMany $query) {
                    return $query->limit(3);
                },
            ])
            ->first();

        if (! $category) {
            return $response->setData([]);
        }

        $products = $productRepository->getProductsByCategories(array_merge([
            'categories' => [
                'by' => 'id',
                'value_in' => array_merge([$category->id], $category->activeChildren->pluck('id')->all()),
            ],
            'take' => $request->integer('limit', 8) ?: 8,
        ], EcommerceHelper::withReviewsParams()));

        $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

        $data = [];
        foreach ($products as $product) {
            $data[] = '<div class="product-inner">' . Theme::partial('ecommerce.product-item', compact('product', 'wishlistIds')) . '</div>';
        }

        return $response->setData($data);
    }
}
