<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Controller;

class CompareController extends Controller
{
    public function __construct(protected ProductInterface $productRepository)
    {
    }

    public function index()
    {
        if (! EcommerceHelper::isCompareEnabled()) {
            abort(404);
        }

        SeoHelper::setTitle(__('Compare'));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Compare'), route('public.compare'));

        $itemIds = collect(Cart::instance('compare')->content())
            ->sortBy([['updated_at', 'desc']])
            ->pluck('id');

        $products = collect();
        $attributeSets = collect();
        if ($itemIds->count()) {
            $products = $this->productRepository
                ->getProductsByIds($itemIds->toArray(), array_merge([
                    'take' => 10,
                    'with' => EcommerceHelper::withProductEagerLoadingRelations(),
                ], EcommerceHelper::withReviewsParams()));

            $attributeSets = ProductAttributeSet::getAllWithSelected($itemIds);
        }

        return Theme::scope(
            'ecommerce.compare',
            compact('products', 'attributeSets'),
            'plugins/ecommerce::themes.compare'
        )->render();
    }

    public function store(int|string $productId, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCompareEnabled()) {
            abort(404);
        }

        $product = Product::query()->findOrFail($productId);

        if ($product->is_variation) {
            $product = $product->original_product;
            $productId = $product->getKey();
        }

        $duplicates = Cart::instance('compare')->search(function ($cartItem) use ($productId) {
            return $cartItem->id == $productId;
        });

        if (! $duplicates->isEmpty()) {
            return $response
                ->setMessage(__(':product is already in your compare list!', ['product' => $product->name]))
                ->setError();
        }

        Cart::instance('compare')->add($productId, $product->name, 1, $product->front_sale_price)
            ->associate(Product::class);

        return $response
            ->setMessage(__('Added product :product to compare list successfully!', ['product' => $product->name]))
            ->setData(['count' => Cart::instance('compare')->count()]);
    }

    public function destroy(int|string $productId, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCompareEnabled()) {
            abort(404);
        }

        $product = Product::query()->findOrFail($productId);

        Cart::instance('compare')->search(function ($cartItem, $rowId) use ($productId) {
            if ($cartItem->id == $productId) {
                Cart::instance('compare')->remove($rowId);

                return true;
            }

            return false;
        });

        return $response
            ->setMessage(__('Removed product :product from compare list successfully!', ['product' => $product->name]))
            ->setData(['count' => Cart::instance('compare')->count()]);
    }
}
