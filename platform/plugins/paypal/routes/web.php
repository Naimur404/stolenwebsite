<?php

use Botble\PayPal\Http\Controllers\PayPalController;
use Illuminate\Support\Facades\Route;

Route::group(['controller' => PayPalController::class, 'middleware' => ['web', 'core']], function () {
    Route::get('payment/paypal/status', 'getCallback')->name('payments.paypal.status');
});
