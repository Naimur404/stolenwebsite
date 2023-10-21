<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Enums\DiscountTargetEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Discount as DiscountModel;
use Illuminate\Support\Arr;

class HandleApplyPromotionsService
{
    public function execute($token = null, array $data = [], ?string $prefix = ''): float|int
    {
        $promotionDiscountAmount = $this->getPromotionDiscountAmount($data);

        if (! $token) {
            $token = OrderHelper::getOrderSessionToken();
        }

        $sessionData = OrderHelper::getOrderSessionData($token);
        Arr::set($sessionData, $prefix . 'promotion_discount_amount', $promotionDiscountAmount);
        OrderHelper::setOrderSessionData($token, $sessionData);

        return $promotionDiscountAmount;
    }

    public function getPromotionDiscountAmount(array $data = [])
    {
        $promotionDiscountAmount = 0;

        $cartInstance = Cart::instance('cart');

        $rawTotal = Arr::get($data, 'rawTotal', $cartInstance->rawTotal());
        $cartItems = Arr::get($data, 'cartItems', $cartInstance->content());
        $countCart = Arr::get($data, 'countCart', $cartInstance->count());
        $productItems = Arr::get($data, 'productItems', $cartInstance->products());

        $availablePromotions = Discount::getAvailablePromotions(false)
            ->reject(fn (DiscountModel $item) => in_array($item->target, [
                DiscountTargetEnum::SPECIFIC_PRODUCT,
                DiscountTargetEnum::PRODUCT_VARIANT,
            ]));

        foreach ($productItems as $product) {
            if (! $product->is_variation) {
                $productCollections = $product->productCollections;
            } else {
                $productCollections = $product->original_product->productCollections;
            }

            $promotion = Discount::promotionForProduct([$product->id], $productCollections->pluck('id')->all());

            if ($promotion) {
                $availablePromotions = $availablePromotions->push($promotion);
            }
        }

        foreach ($availablePromotions as $promotion) {
            switch ($promotion->type_option) {
                case DiscountTypeOptionEnum::AMOUNT:
                    switch ($promotion->target) {
                        case DiscountTargetEnum::MINIMUM_ORDER_AMOUNT:
                            if ($promotion->min_order_price <= $rawTotal) {
                                $promotionDiscountAmount += $promotion->value;
                            }

                            break;
                        case DiscountTargetEnum::ALL_ORDERS:
                            $promotionDiscountAmount += $promotion->value;

                            break;
                        case DiscountTargetEnum::SPECIFIC_PRODUCT:
                        case DiscountTargetEnum::PRODUCT_VARIANT:
                            foreach ($cartItems as $item) {
                                if (
                                    $item->qty >= $promotion->product_quantity &&
                                    in_array($item->id, $promotion->products()->pluck('product_id')->all())
                                ) {
                                    $promotionDiscountAmount += $promotion->value;
                                }
                            }

                            break;
                        default:
                            if ($countCart >= $promotion->product_quantity) {
                                $promotionDiscountAmount += $promotion->value;
                            }

                            break;
                    }

                    break;
                case DiscountTypeOptionEnum::PERCENTAGE:
                    switch ($promotion->target) {
                        case DiscountTargetEnum::MINIMUM_ORDER_AMOUNT:
                            if ($promotion->min_order_price <= $rawTotal) {
                                $promotionDiscountAmount += $rawTotal * $promotion->value / 100;
                            }

                            break;
                        case DiscountTargetEnum::ALL_ORDERS:
                            $promotionDiscountAmount += $rawTotal * $promotion->value / 100;

                            break;
                        case DiscountTargetEnum::SPECIFIC_PRODUCT:
                        case DiscountTargetEnum::PRODUCT_VARIANT:
                            foreach ($cartItems as $item) {
                                if (
                                    $item->qty >= $promotion->product_quantity &&
                                    in_array($item->id, $promotion->products()->pluck('product_id')->all())
                                ) {
                                    $promotionDiscountAmount += $item->price * $promotion->value / 100;
                                }
                            }

                            break;
                        default:
                            if ($countCart >= $promotion->product_quantity) {
                                $promotionDiscountAmount += $rawTotal * $promotion->value / 100;
                            }

                            break;
                    }

                    break;
                case DiscountTypeOptionEnum::SAME_PRICE:
                    if ($promotion->product_quantity > 1 && $countCart >= $promotion->product_quantity) {
                        foreach ($cartItems as $item) {
                            if ($item->qty < $promotion->product_quantity) {
                                continue;
                            }

                            if (in_array($promotion->target, [
                                    DiscountTargetEnum::SPECIFIC_PRODUCT,
                                    DiscountTargetEnum::PRODUCT_VARIANT,
                                ]) &&
                                in_array($item->id, $promotion->products()->pluck('product_id')->all())
                            ) {
                                $promotionDiscountAmount += ($item->price - $promotion->value) * $item->qty;

                                continue;
                            }

                            if ($product = $productItems->firstWhere('id', $item->id)) {
                                $productCollections = $product
                                    ->productCollections()
                                    ->pluck('ec_product_collections.id')->all();

                                $discountProductCollections = $promotion
                                    ->productCollections()
                                    ->pluck('ec_product_collections.id')
                                    ->all();

                                if (! empty(array_intersect($productCollections, $discountProductCollections))) {
                                    $promotionDiscountAmount += ($item->price - $promotion->value) * $item->qty;
                                }
                            }
                        }
                    }

                    break;
            }
        }

        return $promotionDiscountAmount;
    }
}
