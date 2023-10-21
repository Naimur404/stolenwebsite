<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\PayPalPayout\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::post('paypal-payout/make/{withdrawalId}', 'PayPalPayoutController@make')->name('paypal-payout.make');
        Route::get('paypal-payout/retrieve/{batchId}', 'PayPalPayoutController@retrieve')->name('paypal-payout.retrieve');
    });
});
