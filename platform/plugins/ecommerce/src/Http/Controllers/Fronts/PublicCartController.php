<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Requests\CartRequest;
use Botble\Ecommerce\Http\Requests\UpdateCartRequest;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class PublicCartController extends Controller
{
    public function store(CartRequest $request, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        $product = Product::query()->find($request->input('id'));

        if (! $product) {
            return $response
                ->setError()
                ->setMessage(__('This product is out of stock or not exists!'));
        }

        if ($product->variations->count() > 0 && ! $product->is_variation) {
            $product = $product->defaultVariation->product;
        }

        if ($product->isOutOfStock()) {
            return $response
                ->setError()
                ->setMessage(__('Product :product is out of stock!', ['product' => $product->original_product->name ?: $product->name]));
        }

        $maxQuantity = $product->quantity;

        if (! $product->canAddToCart($request->input('qty', 1))) {
            return $response
                ->setError()
                ->setMessage(__('Maximum quantity is :max!', ['max' => $maxQuantity]));
        }

        $product->quantity -= $request->input('qty', 1);

        $outOfQuantity = false;
        foreach (Cart::instance('cart')->content() as $item) {
            if ($item->id == $product->id) {
                $originalQuantity = $product->quantity;
                $product->quantity = (int)$product->quantity - $item->qty;

                if ($product->quantity < 0) {
                    $product->quantity = 0;
                }

                if ($product->isOutOfStock()) {
                    $outOfQuantity = true;

                    break;
                }

                $product->quantity = $originalQuantity;
            }
        }

        if (
            EcommerceHelper::isEnabledProductOptions() &&
            $product->original_product->options()->where('required', true)->exists()
        ) {
            if (! $request->input('options')) {
                return $response
                    ->setError()
                    ->setData(['next_url' => $product->original_product->url])
                    ->setMessage(__('Please select product options!'));
            }

            $requiredOptions = $product->original_product->options()->where('required', true)->get();

            $message = null;

            foreach ($requiredOptions as $requiredOption) {
                if (! $request->input('options.' . $requiredOption->id . '.values')) {
                    $message .= trans('plugins/ecommerce::product-option.add_to_cart_value_required', ['value' => $requiredOption->name]);
                }
            }

            if ($message) {
                return $response
                    ->setError()
                    ->setMessage(__('Please select product options!'));
            }
        }

        if ($outOfQuantity) {
            return $response
                ->setError()
                ->setMessage(__('Product :product is out of stock!', ['product' => $product->original_product->name ?: $product->name]));
        }

        $cartItems = OrderHelper::handleAddCart($product, $request);

        $response
            ->setMessage(__(
                'Added product :product to cart successfully!',
                ['product' => $product->original_product->name ?: $product->name]
            ));

        $token = OrderHelper::getOrderSessionToken();

        $nextUrl = route('public.checkout.information', $token);

        if (EcommerceHelper::getQuickBuyButtonTarget() == 'cart') {
            $nextUrl = route('public.cart');
        }

        if ($request->input('checkout')) {
            $response->setData(['next_url' => $nextUrl]);

            if ($request->ajax() && $request->wantsJson()) {
                return $response;
            }

            return $response
                ->setNextUrl($nextUrl);
        }

        return $response
            ->setData([
                'status' => true,
                'count' => Cart::instance('cart')->count(),
                'total_price' => format_price(Cart::instance('cart')->rawSubTotal()),
                'content' => $cartItems,
            ]);
    }

    public function getView(HandleApplyPromotionsService $applyPromotionsService)
    {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        Theme::asset()
            ->container('footer')
            ->add('ecommerce-checkout-js', 'vendor/core/plugins/ecommerce/js/checkout.js', ['jquery']);

        $promotionDiscountAmount = 0;
        $couponDiscountAmount = 0;

        $products = [];
        $crossSellProducts = collect();

        if (Cart::instance('cart')->count() > 0) {
            $products = Cart::instance('cart')->products();

            $promotionDiscountAmount = $applyPromotionsService->execute();

            $sessionData = OrderHelper::getOrderSessionData();

            if (session()->has('applied_coupon_code')) {
                $couponDiscountAmount = Arr::get($sessionData, 'coupon_discount_amount', 0);
            }

            $parentIds = $products->pluck('original_product.id')->toArray();

            $crossSellProducts = get_cart_cross_sale_products($parentIds, (int)theme_option('number_of_cross_sale_product', 4));
        }

        SeoHelper::setTitle(__('Shopping Cart'));

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Shopping Cart'), route('public.cart'));

        return Theme::scope(
            'ecommerce.cart',
            compact('promotionDiscountAmount', 'couponDiscountAmount', 'products', 'crossSellProducts'),
            'plugins/ecommerce::themes.cart'
        )->render();
    }

    public function postUpdate(UpdateCartRequest $request, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        if ($request->has('checkout')) {
            $token = OrderHelper::getOrderSessionToken();

            return $response->setNextUrl(route('public.checkout.information', $token));
        }

        $data = $request->input('items', []);

        $outOfQuantity = false;
        foreach ($data as $item) {
            $cartItem = Cart::instance('cart')->get($item['rowId']);

            if (! $cartItem) {
                continue;
            }

            $product = Product::query()->find($cartItem->id);

            if ($product) {
                $originalQuantity = $product->quantity;
                $product->quantity = (int)$product->quantity - (int)Arr::get($item, 'values.qty', 0) + 1;

                if ($product->quantity < 0) {
                    $product->quantity = 0;
                }

                if ($product->isOutOfStock()) {
                    $outOfQuantity = true;
                } else {
                    Cart::instance('cart')->update($item['rowId'], Arr::get($item, 'values'));
                }

                $product->quantity = $originalQuantity;
            }
        }

        if ($outOfQuantity) {
            return $response
                ->setError()
                ->setData([
                    'count' => Cart::instance('cart')->count(),
                    'total_price' => format_price(Cart::instance('cart')->rawSubTotal()),
                    'content' => Cart::instance('cart')->content(),
                ])
                ->setMessage(__('One or all products are not enough quantity so cannot update!'));
        }

        return $response
            ->setData([
                'count' => Cart::instance('cart')->count(),
                'total_price' => format_price(Cart::instance('cart')->rawSubTotal()),
                'content' => Cart::instance('cart')->content(),
            ])
            ->setMessage(__('Update cart successfully!'));
    }

    public function getRemove(string $id, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        try {
            Cart::instance('cart')->remove($id);
        } catch (Exception) {
            return $response->setError()->setMessage(__('Cart item is not existed!'));
        }

        return $response
            ->setData([
                'count' => Cart::instance('cart')->count(),
                'total_price' => format_price(Cart::instance('cart')->rawSubTotal()),
                'content' => Cart::instance('cart')->content(),
            ])
            ->setMessage(__('Removed item from cart successfully!'));
    }

    public function getDestroy(BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        Cart::instance('cart')->destroy();

        return $response
            ->setData(Cart::instance('cart')->content())
            ->setMessage(__('Empty cart successfully!'));
    }
}
