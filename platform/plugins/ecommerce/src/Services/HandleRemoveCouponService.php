<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Discount;
use Illuminate\Support\Arr;

class HandleRemoveCouponService
{
    public function execute(string|null $prefix = '', bool $isForget = true): array
    {
        if (! session()->has('applied_coupon_code')) {
            return [
                'error' => true,
                'message' => trans('plugins/ecommerce::discount.not_used'),
            ];
        }

        $couponCode = session('applied_coupon_code');

        $discount = Discount::query()
            ->where('code', $couponCode)
            ->where('type', DiscountTypeEnum::COUPON)
            ->first();

        $token = OrderHelper::getOrderSessionToken();

        $sessionData = OrderHelper::getOrderSessionData($token);

        if ($discount && $discount->type_option === DiscountTypeOptionEnum::SHIPPING) {
            Arr::set($sessionData, $prefix . 'is_free_shipping', false);
        }

        Arr::set($sessionData, $prefix . 'coupon_discount_amount', 0);
        OrderHelper::setOrderSessionData($token, $sessionData);

        if ($isForget) {
            session()->forget('applied_coupon_code');
        }

        return [
            'error' => false,
        ];
    }
}
