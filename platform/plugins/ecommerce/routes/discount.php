<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'discounts', 'as' => 'discounts.'], function () {
            Route::resource('', 'DiscountController')->parameters(['' => 'discount']);

            Route::post('generate-coupon', [
                'as' => 'generate-coupon',
                'uses' => 'DiscountController@postGenerateCoupon',
                'permission' => 'discounts.create',
            ]);
        });
    });
});

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers\Fronts', 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::group(['prefix' => 'coupon', 'as' => 'public.coupon.'], function () {
            Route::post('apply', [
                'as' => 'apply',
                'uses' => 'PublicCheckoutController@postApplyCoupon',
            ]);

            Route::post('remove', [
                'as' => 'remove',
                'uses' => 'PublicCheckoutController@postRemoveCoupon',
            ]);
        });
    });
});
