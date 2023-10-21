<?php

namespace Botble\Marketplace\Providers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount as DiscountFacade;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Order as OrderModel;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleRemoveCouponService;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\CategoryCommission;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Botble\PayPal\Services\Gateways\PayPalPaymentService;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderSupportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            add_filter(HANDLE_PROCESS_ORDER_DATA_ECOMMERCE, [$this, 'handleProcessOrder'], 100, 4);
            add_filter(HANDLE_PROCESS_POST_CHECKOUT_ORDER_DATA_ECOMMERCE, [$this, 'processPostCheckoutOrder'], 100, 5);
            add_filter(PROCESS_GET_CHECKOUT_SUCCESS_IN_ORDER, [$this, 'processGetCheckoutSuccess'], 100);
            add_filter(PROCESS_GET_PAYMENT_STATUS_ORDER, [$this, 'processGetPaymentStatus'], 100, 2);
            add_filter(SEND_MAIL_AFTER_PROCESS_ORDER_MULTI_DATA, [$this, 'sendMailAfterProcessOrder'], 100);
            add_filter(PROCESS_CHECKOUT_ORDER_DATA_ECOMMERCE, [$this, 'processShippingDiscountOrderData'], 100, 4);
            add_filter(RENDER_PRODUCTS_IN_CHECKOUT_PAGE, [$this, 'renderProductsInCheckoutPage'], 100);
            add_filter(HANDLE_POST_APPLY_COUPON_CODE_ECOMMERCE, [$this, 'processApplyCouponCode'], 100, 2);
            add_filter(HANDLE_POST_REMOVE_COUPON_CODE_ECOMMERCE, [$this, 'processRemoveCouponCode'], 100, 2);
            add_filter(PROCESS_POST_SAVE_INFORMATION_CHECKOUT_ECOMMERCE, [$this, 'processPostSaveInformation'], 100, 3);
            add_filter(PROCESS_GET_CHECKOUT_RECOVER_ECOMMERCE, [$this, 'processGetCheckoutRecover'], 100, 2);
            add_filter(PROCESS_CHECKOUT_RULES_REQUEST_ECOMMERCE, [$this, 'processCheckoutRulesRequest'], 100);
            add_filter(PROCESS_CHECKOUT_MESSAGES_REQUEST_ECOMMERCE, [$this, 'processCheckoutMessagesRequest'], 100);
            add_action(ACTION_AFTER_ORDER_STATUS_COMPLETED_ECOMMERCE, [$this, 'afterOrderStatusCompleted'], 12);
            add_filter(ACTION_AFTER_ORDER_RETURN_STATUS_COMPLETED, [$this, 'afterReturnOrderCompleted'], 12);
        });
    }

    public function renderProductsInCheckoutPage(array|string|EloquentCollection $products): string|array|Collection
    {
        if ($products instanceof Collection) {
            $groupedProducts = $this->cartGroupByStore($products);
            $token = OrderHelper::getOrderSessionToken();
            $sessionCheckoutData = OrderHelper::getOrderSessionData($token);

            return view(
                'plugins/marketplace::orders.checkout.products',
                compact('groupedProducts', 'sessionCheckoutData')
            )->render();
        }

        return $products;
    }

    protected function cartGroupByStore(EloquentCollection $products): array|Collection
    {
        if (! $products->count()) {
            return $products;
        }

        $products->loadMissing([
            'variationInfo',
            'variationInfo.configurableProduct',
            'variationInfo.configurableProduct.store',
        ]);

        $groupedProducts = collect();
        foreach ($products as $product) {
            $storeId = ($product->original_product && $product->original_product->store_id) ? $product->original_product->store_id : 0;
            if (! Arr::has($groupedProducts, $storeId)) {
                $groupedProducts[$storeId] = collect([
                    'store' => $product->original_product->store,
                    'products' => collect([$product]),
                ]);
            } else {
                $groupedProducts[$storeId]['products'][] = $product;
            }
        }

        return $groupedProducts;
    }

    public function processPostCheckoutOrder(
        array|EloquentCollection $products,
        Request $request,
        string $token,
        array $sessionCheckoutData,
        BaseHttpResponse $response
    ) {
        $groupedProducts = $this->cartGroupByStore($products);

        $currentUserId = 0;
        if (auth('customer')->check()) {
            $currentUserId = auth('customer')->id();
        }

        $orders = collect();

        $discounts = collect();
        $couponCode = session('applied_coupon_code');

        $preOrders = collect();

        $mpSessionData = Arr::get($sessionCheckoutData, 'marketplace', []);

        if ($couponCode) {
            $this->processApplyCouponCode([], $request);
            $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
            $couponCode = session()->get('applied_coupon_code');
        } else {
            foreach ($mpSessionData as &$storeCheckoutData) {
                Arr::set($storeCheckoutData, 'coupon_discount_amount', 0);
                Arr::set($storeCheckoutData, 'applied_coupon_code', null);
                Arr::set($storeCheckoutData, 'is_free_shipping', false);
            }
            $sessionCheckoutData = OrderHelper::setOrderSessionData($token, ['marketplace' => $mpSessionData]);
        }

        $mpSessionData = Arr::get($sessionCheckoutData, 'marketplace', []);

        $orderIds = collect($mpSessionData ?: [])->pluck('created_order_id');

        if ($orderIds) {
            $preOrders = Order::query()->whereIn('id', $orderIds)->get();
        }

        $foundOrderIds = [];

        $promotionService = $this->app->make(HandleApplyPromotionsService::class);
        $shippingFeeService = $this->app->make(HandleShippingFeeService::class);
        $applyCouponService = $this->app->make(HandleApplyCouponService::class);

        foreach ($groupedProducts as $storeId => $productsInStore) {
            $sessionStoreData = Arr::get($mpSessionData, $storeId, []);

            $order = $preOrders->firstWhere('store_id', $storeId);
            if ($order) {
                $foundOrderIds[] = $storeId;
            }

            $orders[$storeId] = $this->handleCheckoutOrderByStore(
                $sessionCheckoutData,
                $productsInStore,
                $token,
                $sessionStoreData,
                $request,
                $currentUserId,
                $order,
                $storeId,
                $discounts,
                $promotionService,
                $shippingFeeService,
                $applyCouponService
            );
        }

        // Remove orders not exists pre checkout
        if ($preOrders) {
            foreach ($preOrders as $order) {
                if (! in_array($order->store_id, $foundOrderIds)) {
                    $order->delete();
                    if ($order->address && $order->address->id) {
                        $order->address->delete();
                    }
                }
            }
        }

        if ($couponCode && $discounts->count()) {
            DiscountFacade::getFacadeRoot()->afterOrderPlaced($couponCode);
        }

        $totalAmount = format_price($orders->pluck('amount')->sum(), null, true); // Calculator in here

        do_action('ecommerce_before_processing_payment', $products, $request, $token, $mpSessionData);

        $paymentData = $this->processPaymentMethodPostCheckout($request, (float)$totalAmount);

        if ($checkoutUrl = Arr::get($paymentData, 'checkoutUrl')) {
            return $response
                ->setError($paymentData['error'])
                ->setNextUrl($checkoutUrl)
                ->setData(['checkoutUrl' => $checkoutUrl])
                ->withInput()
                ->setMessage($paymentData['message']);
        }

        if ($paymentData['error'] || ! $paymentData['charge_id']) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL($token))
                ->withInput()
                ->setMessage($paymentData['message'] ?: __('Checkout error!'));
        }

        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL($token))
            ->setMessage(__('Checkout successfully!'));
    }

    public function processApplyCouponCode(array $result, Request $request): array
    {
        /**
         * @var EloquentCollection $products
         */
        $products = Cart::instance('cart')->products();
        $groupedProducts = $this->cartGroupByStore($products);
        $token = OrderHelper::getOrderSessionToken();

        if (! $token) {
            $token = OrderHelper::getOrderSessionToken();
        }

        $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
        $sessionMarketplaceData = Arr::get($sessionCheckoutData, 'marketplace', []);
        $results = collect();
        $couponCode = $request->input('coupon_code');

        if (! $couponCode) {
            $couponCode = session('applied_coupon_code');
        }

        foreach ($groupedProducts as $storeId => $groupedProduct) {
            $productItems = $groupedProduct['products'];
            $cartItems = $productItems->pluck('cartItem');
            $rawTotal = Cart::instance('cart')->rawTotalByItems($cartItems);
            $countCart = Cart::instance('cart')->countByItems($cartItems);
            $sessionData = Arr::get($sessionMarketplaceData, $storeId, []);
            $prefix = "marketplace.$storeId.";
            $result = $this->app->make(HandleApplyCouponService::class)
                ->execute(
                    $couponCode,
                    $sessionData,
                    compact('cartItems', 'rawTotal', 'countCart', 'productItems'),
                    $prefix
                );
            $results[$storeId] = $result;
        }

        $error = 0;
        $message = '';
        $successData = [
            'error' => true,
            'data' => [],
        ];

        $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
        $sessionMarketplaceData = Arr::get($sessionCheckoutData, 'marketplace', []);

        foreach ($results as $storeId => $result) {
            $sessionData = Arr::get($sessionMarketplaceData, $storeId, []);
            if (Arr::get($result, 'error')) {
                $error += 1;
                $message = Arr::get($result, 'message');

                Arr::set($sessionData, 'coupon_discount_amount', 0);
                Arr::set($sessionData, 'applied_coupon_code', null);
                Arr::set($sessionData, 'is_free_shipping', false);
            } else {
                $discount = Arr::get($result, 'data.discount');
                if ((! $discount->store_id || $discount->store_id == $storeId) &&
                    (Arr::get($result, 'data.is_free_shipping', false) || Arr::get($result, 'data.discount_amount'))) {
                    $successData = $result;
                    Arr::set($sessionData, 'applied_coupon_code', $couponCode);
                    Arr::set($sessionData, 'coupon_discount_amount', Arr::get($result, 'data.discount_amount'));
                } else {
                    Arr::set($sessionData, 'coupon_discount_amount', 0);
                    Arr::set($sessionData, 'applied_coupon_code', null);
                    Arr::set($sessionData, 'is_free_shipping', false);
                    $message = __('Coupon code is not valid or does not apply to the products');
                    $error += 1;
                }
            }

            Arr::set($sessionMarketplaceData, $storeId, $sessionData);
        }

        // return if all are error
        if ($results->count() == $error) {
            session()->forget('applied_coupon_code');

            return compact('error', 'message');
        }

        $couponDiscountAmount = collect($sessionMarketplaceData)->sum('coupon_discount_amount');

        OrderHelper::setOrderSessionData($token, [
            'marketplace' => $sessionMarketplaceData,
            'coupon_discount_amount' => $couponDiscountAmount,
        ]);

        return $successData;
    }

    public function handleCheckoutOrderByStore(
        array $sessionCheckoutData,
        array|Collection $products,
        string $token,
        array $sessionStoreData,
        Request $request,
        int|string|null $currentUserId,
        Order|null $order,
        int|string|null $storeId,
        array|Collection &$discounts,
        HandleApplyPromotionsService $promotionService,
        HandleShippingFeeService $shippingFeeService,
        HandleApplyCouponService $applyCouponService
    ) {
        $shippingAmount = 0;

        $cartItems = $products['products']->pluck('cartItem');
        $rawTotal = Cart::instance('cart')->rawTotalByItems($cartItems);
        $countCart = Cart::instance('cart')->countByItems($cartItems);
        $couponCode = Arr::get($sessionStoreData, 'applied_coupon_code');

        $isAvailableShipping = EcommerceHelper::isAvailableShipping($products['products']);
        $shippingMethodInput = $request->input("shipping_method.$storeId", ShippingMethodEnum::DEFAULT);

        $promotionDiscountAmount = $promotionService
            ->execute($token, compact('cartItems', 'rawTotal', 'countCart'), "marketplace.$storeId.");

        $couponDiscountAmount = 0;
        if ($couponCode) {
            $couponDiscountAmount = Arr::get($sessionStoreData, 'coupon_discount_amount', 0);
        }

        $paymentMethod = session('selected_payment_method');
        $orderAmount = max($rawTotal - $promotionDiscountAmount - $couponDiscountAmount, 0);

        $shippingData = [];
        $shippingMethod = [];
        if ($isAvailableShipping) {
            $shippingData = $this->getShippingData($sessionStoreData, $orderAmount, $products, $paymentMethod);

            $shippingMethodData = $shippingFeeService
                ->execute(
                    $shippingData,
                    $shippingMethodInput,
                    $request->input("shipping_option.$storeId")
                );

            $shippingMethod = Arr::first($shippingMethodData);
            if (! $shippingMethod) {
                throw ValidationException::withMessages([
                    'shipping_method.' . $storeId => trans(
                        'validation.exists',
                        ['attribute' => trans('plugins/ecommerce::shipping.shipping_method')]
                    ),
                ]);
            }

            $shippingAmount = Arr::get($shippingMethod, 'price', 0);

            if (get_shipping_setting('free_ship', $shippingMethodInput)) {
                $shippingAmount = 0;
            }
        }

        if ($couponCode) {
            $discount = $applyCouponService->getCouponData($couponCode, $sessionStoreData);
            if ($discount) {
                if (! $discount->store_id || $discount->store_id == $storeId) {
                    $discounts->push($discount);
                    $shippingAmount = Arr::get($sessionStoreData, 'is_free_shipping') ? 0 : $shippingAmount;
                }
            }
        }

        $orderAmount += (float)$shippingAmount;

        $data = array_merge($request->input(), [
            'amount' => $orderAmount,
            'currency' => $request->input('currency', strtoupper(get_application_currency()->title)),
            'user_id' => $currentUserId,
            'shipping_method' => $isAvailableShipping ? $shippingMethodInput : '',
            'shipping_option' => $isAvailableShipping ? $request->input("shipping_option.$storeId") : null,
            'shipping_amount' => (float)$shippingAmount,
            'tax_amount' => Cart::instance('cart')->rawTaxByItems($cartItems),
            'sub_total' => Cart::instance('cart')->rawSubTotalByItems($cartItems),
            'coupon_code' => $couponCode,
            'discount_amount' => $promotionDiscountAmount + $couponDiscountAmount,
            'status' => OrderStatusEnum::PENDING,
            'token' => $token,
        ]);

        if ($order) {
            $order->fill($data);
            $order->save();
        } else {
            $order = Order::query()->create($data);
        }

        if ($isAvailableShipping) {
            Shipment::query()->create([
                'order_id' => $order->id,
                'user_id' => 0,
                'weight' => $shippingData ? Arr::get($shippingData, 'weight') : 0,
                'cod_amount' => ($order->payment->id && $order->payment->status != PaymentStatusEnum::COMPLETED) ? $order->amount : 0,
                'cod_status' => ShippingCodStatusEnum::PENDING,
                'type' => $order->shipping_method,
                'status' => ShippingStatusEnum::PENDING,
                'price' => $order->shipping_amount,
                'store_id' => $order->store_id,
                'rate_id' => $shippingData ? Arr::get($shippingMethod, 'id', '') : '',
                'shipment_id' => $shippingData ? Arr::get($shippingMethod, 'shipment_id', '') : '',
                'shipping_company_name' => $shippingData ? Arr::get($shippingMethod, 'company_name') : '',
            ]);
        }

        // Address Order in here
        $addressKeys = [
            'name',
            'phone',
            'email',
            'country',
            'state',
            'city',
            'address',
            'zip_code',
            'address_id',
            'billing_address_same_as_shipping_address',
            'billing_address',
        ];
        $addressData = Arr::only($sessionCheckoutData, $addressKeys);
        $sessionStoreData = array_merge($sessionStoreData, $addressData);
        $sessionStoreData['created_order_id'] = $order->id;
        OrderHelper::processAddressOrder($currentUserId, $sessionStoreData, $request);

        OrderHistory::query()->create([
            'action' => 'create_order_from_payment_page',
            'description' => __('Order is created from checkout page'),
            'order_id' => $order->id,
        ]);

        OrderHelper::processOrderProductData($products, $sessionStoreData);

        $request->merge([
            'order_id' => array_merge($request->input('order_id', []), [$order->id]),
        ]);

        return $order;
    }

    public function getShippingData(
        array $session,
        int|float $orderTotal,
        array|Collection $products,
        ?string $paymentMethod = null
    ): array {
        if ($products['store'] && $products['store']->id) {
            $keys = ['name', 'company', 'address', 'country', 'state', 'city', 'zip_code', 'email', 'phone'];
            $origin = Arr::only($products['store']->toArray(), $keys);
            if (! EcommerceHelper::isUsingInMultipleCountries()) {
                $origin['country'] = EcommerceHelper::getFirstCountryId();
            }
        } else {
            $origin = EcommerceHelper::getOriginAddress();
        }

        return EcommerceHelper::getShippingData($products['products'], $session, $origin, $orderTotal, $paymentMethod);
    }

    public function processPaymentMethodPostCheckout(Request $request, int|float $totalAmount): array
    {
        $paymentData = [
            'error' => false,
            'message' => false,
            'amount' => round((float)$totalAmount, 2),
            'currency' => $request->input('currency', strtoupper(cms_currency()->getDefaultCurrency()->title)),
            'type' => $request->input('payment_method'),
            'charge_id' => null,
        ];

        return apply_filters(FILTER_ECOMMERCE_PROCESS_PAYMENT, $paymentData, $request);
    }

    public function processGetCheckoutSuccess(string $token)
    {
        $orders = Order::query()
            ->where('token', $token)
            ->with(['address', 'products'])
            ->get();

        if (! $orders->count()) {
            abort(404);
        }

        if ($orders->where('is_finished', false)->count()) {
            foreach ($orders->where('is_finished', false) as $order) {
                if (! $order->payment_id) {
                    continue;
                }

                $order->is_finished = true;
                $order->save();

                OrderHelper::decreaseProductQuantity($order);
            }
        }

        OrderHelper::clearSessions($token);

        return view('plugins/marketplace::orders.thank-you', compact('orders'));
    }

    public function processGetPaymentStatus(Request $request, BaseHttpResponse $response)
    {
        $token = session('tracked_start_checkout');

        if (! $token) {
            return $response->setNextUrl(route('public.index'));
        }

        $this->app->make(PayPalPaymentService::class)->afterMakePayment($request->input());

        return $response
            ->setNextUrl(route('public.checkout.success', $token))
            ->setMessage(__('Checkout successfully!'));
    }

    public function sendMailAfterProcessOrder(Collection $orders): Collection
    {
        try {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('admin_new_order')) {
                $this->setEmailVariables($orders);
                $mailer->sendUsingTemplate('admin_new_order');
            }

            $this->sendOrderConfirmationEmail($orders, true);
        } catch (Throwable $exception) {
            info($exception->getMessage());
        }

        MarketplaceHelper::sendMailToVendorAfterProcessingOrder($orders);

        return $orders;
    }

    public function setEmailVariables(Collection $orders): \Botble\Base\Supports\EmailHandler
    {
        $theFirst = $orders->first();

        $customerAddress = $theFirst->full_address;

        return EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'store_address' => get_ecommerce_setting('store_address'),
                'store_phone' => get_ecommerce_setting('store_phone'),
                'order_id' => $theFirst->code,
                'order_token' => $theFirst->token,
                'customer_name' => $theFirst->user->name ?: $theFirst->address->name,
                'customer_email' => $theFirst->user->email ?: $theFirst->address->email,
                'customer_phone' => $theFirst->user->phone ?: $theFirst->address->phone,
                'customer_address' => $customerAddress,
                'product_list' => view('plugins/marketplace::emails.partials.order-detail', compact('orders'))
                    ->render(),
                'shipping_method' => $theFirst->shipping_method_name,
                'payment_method' => $theFirst->payment->payment_channel->label(),
            ]);
    }

    public function sendOrderConfirmationEmail(Collection $orders, bool $saveHistory = false): bool
    {
        try {
            $theFirst = $orders->first();
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('customer_new_order')) {
                $this->setEmailVariables($orders);

                EmailHandler::send(
                    $mailer->getTemplateContent('customer_new_order'),
                    $mailer->getTemplateSubject('customer_new_order'),
                    $theFirst->user->email ?: $theFirst->address->email
                );

                if ($saveHistory) {
                    foreach ($orders as $order) {
                        OrderHistory::query()->create([
                            'action' => 'send_order_confirmation_email',
                            'description' => trans('plugins/ecommerce::order.confirmation_email_was_sent_to_customer'),
                            'order_id' => $order->id,
                        ]);
                    }
                }
            }

            return true;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return false;
    }

    public function processShippingDiscountOrderData(
        array|EloquentCollection $products,
        string $token,
        array $sessionCheckoutData,
        Request $request
    ): array {
        $groupedProducts = $this->cartGroupByStore($products);

        $mpSessionCheckoutData = Arr::get($sessionCheckoutData, 'marketplace');

        $couponCode = session('applied_coupon_code');
        if ($couponCode) {
            $this->processApplyCouponCode([], $request);
            $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
            $couponCode = session()->get('applied_coupon_code');
        } else {
            foreach ($mpSessionCheckoutData as &$storeCheckoutData) {
                Arr::set($storeCheckoutData, 'coupon_discount_amount', 0);
                Arr::set($storeCheckoutData, 'applied_coupon_code', null);
                Arr::set($storeCheckoutData, 'is_free_shipping', false);
            }

            $sessionCheckoutData['marketplace'] = $mpSessionCheckoutData;

            OrderHelper::setOrderSessionData($token, $sessionCheckoutData);
        }

        $mpSessionCheckoutData = Arr::get($sessionCheckoutData, 'marketplace');
        $discounts = collect();

        $marketplaceData = collect();

        $promotionService = $this->app->make(HandleApplyPromotionsService::class);
        $shippingFeeService = $this->app->make(HandleShippingFeeService::class);
        $applyCouponService = $this->app->make(HandleApplyCouponService::class);

        $shippingHasValue = []; // use to show shipping amount
        $shipping = [];
        $paymentMethod = session('selected_payment_method');

        $shippingAmount = 0;
        $defaultShippingMethod = null;
        $defaultShippingOption = null;

        foreach ($groupedProducts as $storeId => $productsByStore) {
            $cartItems = $productsByStore['products']->pluck('cartItem');
            $productItems = $productsByStore['products'];
            $vendorSessionData = Arr::get($mpSessionCheckoutData, $storeId);

            $rawTotal = Cart::instance('cart')->rawTotalByItems($cartItems);
            $countCart = Cart::instance('cart')->countByItems($cartItems);

            $prefixPromotion = "marketplace.$storeId.";
            $promotionDiscountAmount = $promotionService->execute(
                $token,
                compact('cartItems', 'rawTotal', 'countCart', 'productItems'),
                $prefixPromotion
            );

            $couponDiscountAmount = 0;
            if ($couponCode) {
                $couponDiscountAmount = Arr::get($vendorSessionData, 'coupon_discount_amount', 0);
            }

            $orderTotal = $rawTotal - $promotionDiscountAmount - $couponDiscountAmount;
            $orderTotal = max($orderTotal, 0);
            $isAvailableShipping = EcommerceHelper::isAvailableShipping($productsByStore['products']);

            $defaultShippingMethod = $request->input("shipping_method.$storeId");
            $defaultShippingOption = null;
            if ($isAvailableShipping) {
                $shippingData = $this->getShippingData(
                    $sessionCheckoutData,
                    $orderTotal,
                    $productsByStore,
                    $paymentMethod
                );

                $shipping = $shippingFeeService->execute($shippingData);

                foreach ($shipping as $key => &$shipItem) {
                    if (get_shipping_setting('free_ship', $key)) {
                        foreach ($shipItem as &$subShippingItem) {
                            Arr::set($subShippingItem, 'price', 0);
                        }
                    }
                }

                if (! $defaultShippingMethod) {
                    $defaultShippingMethod = old(
                        "shipping_method.$storeId",
                        Arr::get($vendorSessionData, 'shipping_method', Arr::first(array_keys($shipping)))
                    );
                }

                if (! empty($shipping)) {
                    if (! $shippingHasValue) {
                        $shippingHasValue = $shipping;
                    }

                    $defaultShippingOption = Arr::first(array_keys(Arr::first($shipping)));

                    if ($optionRequest = $request->input(
                        "shipping_option.$storeId",
                        old("shipping_option.$storeId")
                    )) {
                        $defaultShippingOption = $optionRequest;
                    } else {
                        $defaultShippingOption = Arr::get(
                            $vendorSessionData,
                            'shipping_option',
                            $defaultShippingOption
                        );
                    }
                }

                $defaultShippingOption = BaseHelper::stringify($defaultShippingOption);
                $defaultShippingMethod = BaseHelper::stringify($defaultShippingMethod);

                $shippingAmount = Arr::get($shipping, "$defaultShippingMethod.$defaultShippingOption.price", 0);

                Arr::set($vendorSessionData, 'shipping_method', $defaultShippingMethod);
                Arr::set($vendorSessionData, 'shipping_option', $defaultShippingOption);
                Arr::set($vendorSessionData, 'shipping_amount', $shippingAmount);
            }

            $sessionCheckoutData['marketplace'] = [$storeId => $vendorSessionData];

            OrderHelper::setOrderSessionData($token, $sessionCheckoutData);

            if ($couponCode) {
                if (! $request->input('applied_coupon')) {
                    $discount = $applyCouponService->getCouponData($couponCode, $vendorSessionData);

                    if ($discount) {
                        if (! $discount->store_id || $discount->store_id == $storeId) {
                            $discounts->push($discount);
                            $shippingAmount = Arr::get($vendorSessionData, 'is_free_shipping') ? 0 : $shippingAmount;
                        }
                    }
                } else {
                    $shippingAmount = Arr::get($vendorSessionData, 'is_free_shipping') ? 0 : $shippingAmount;
                }
            }

            if (! $isAvailableShipping) {
                $shippingAmount = 0;
            }

            $marketplaceData[$storeId] = [
                'shipping' => $shipping,
                'default_shipping_method' => $defaultShippingMethod,
                'default_shipping_option' => $defaultShippingOption,
                'shipping_amount' => $shippingAmount,
                'promotion_discount_amount' => $promotionDiscountAmount,
                'coupon_discount_amount' => $couponDiscountAmount,
                'is_available_shipping' => $isAvailableShipping,
            ];
        }

        $shippingAmount = $marketplaceData->pluck('shipping_amount')->sum();
        $promotionDiscountAmount = $marketplaceData->pluck('promotion_discount_amount')->sum();
        $couponDiscountAmount = $marketplaceData->pluck('coupon_discount_amount')->sum();

        $sessionCheckoutData = OrderHelper::getOrderSessionData($token);

        $mpSessionCheckoutData = Arr::get($sessionCheckoutData, 'marketplace');

        foreach ($mpSessionCheckoutData as $storeId => $mpSessionData) {
            Arr::set(
                $mpSessionCheckoutData,
                $storeId,
                array_merge($mpSessionData, Arr::get($marketplaceData, $storeId, []))
            );
        }

        $sessionCheckoutData = OrderHelper::mergeOrderSessionData($token, ['marketplace' => $mpSessionCheckoutData]);
        $sessionCheckoutData['is_available_shipping'] = $marketplaceData->where('is_available_shipping')->count();
        if ($sessionCheckoutData['is_available_shipping']) {
            $shipping = $shippingHasValue;
        }

        return [
            $sessionCheckoutData,
            $shipping,
            $defaultShippingMethod,
            $defaultShippingOption,
            $shippingAmount,
            $promotionDiscountAmount,
            $couponDiscountAmount,
        ];
    }

    public function processRemoveCouponCode(array|EloquentCollection $products): array
    {
        $groupedProducts = $this->cartGroupByStore($products);

        $results = collect();

        foreach ($groupedProducts as $storeId => $groupedProduct) {
            $prefix = "marketplace.$storeId.";
            $result = $this->app->make(HandleRemoveCouponService::class)->execute($prefix, false);
            $results[$storeId] = $result;
        }

        session()->forget('applied_coupon_code');

        $error = 0;
        $message = '';
        $successData = [
            'error' => true,
            'data' => [],
        ];

        foreach ($results as $result) {
            if (Arr::get($result, 'error')) {
                $error += 1;
                $message = Arr::get($result, 'message');
            } else {
                $successData = $result;
            }
        }

        // return if all are error
        if ($results->count() == $error) {
            return compact('error', 'message');
        }

        return $successData;
    }

    public function processPostSaveInformation(array $sessionCheckoutData, Request $request, string $token): array
    {
        if (session()->has('applied_coupon_code')) {
            $discounts = collect();
            $mpSessionData = Arr::get($sessionCheckoutData, 'marketplace', []);
            foreach ($mpSessionData as $storeId => $sessionStoreData) {
                $discount = $this->app->make(HandleApplyCouponService::class)
                    ->getCouponData(session('applied_coupon_code'), $sessionStoreData);
                if (! $discount) {
                    $discounts->push($discount);
                    $prefix = "marketplace.$storeId.";
                    $this->app->make(HandleRemoveCouponService::class)->execute($prefix, false);
                }
            }

            if (count($mpSessionData) == $discounts->count()) {
                session()->forget('applied_coupon_code');
            }

            $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
        }

        $mpSessionData = Arr::get($sessionCheckoutData, 'marketplace', []);

        $addressKeys = [
            'name',
            'phone',
            'email',
            'country',
            'state',
            'city',
            'address',
            'zip_code',
            'address_id',
            'billing_address_same_as_shipping_address',
            'billing_address',
        ];
        $addressData = Arr::only((array)$request->input('address', []), $addressKeys);

        foreach ($mpSessionData as $storeId => $sessionStoreData) {
            Arr::set($mpSessionData, $storeId, array_merge($sessionStoreData, $addressData));
        }

        Arr::set($sessionCheckoutData, 'marketplace', $mpSessionData);

        return $sessionCheckoutData;
    }

    public function processGetCheckoutRecover(string $token, Request $request)
    {
        $orders = Order::query()
            ->where([
                'token' => $token,
                'is_finished' => 0,
            ])
            ->with(['address', 'products'])
            ->get();

        if (! $orders->count()) {
            abort(404);
        }

        if (session()->has('tracked_start_checkout') && session('tracked_start_checkout') == $token) {
            $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
        } else {
            $token = OrderHelper::getOrderSessionToken();

            $trashOrders = Order::query()
                ->with('address')
                ->where([
                    'token' => $token,
                    'is_finished' => 0,
                ])
                ->get();

            foreach ($trashOrders as $trashOrder) {
                $trashOrder->delete();
                if ($trashOrder->address && $trashOrder->address->id) {
                    $trashOrder->address->delete();
                }
            }

            $order = $orders->first();
            $sessionCheckoutData = [
                'name' => $order->address->name,
                'email' => $order->address->email,
                'phone' => $order->address->phone,
                'address' => $order->address->address,
                'country' => $order->address->country,
                'state' => $order->address->state,
                'city' => $order->address->city,
                'zip_code' => $order->address->zip_code,
                'shipping_method' => $order->shipping_method,
                'shipping_option' => $order->shipping_option,
                'shipping_amount' => $order->shipping_amount,
            ];
            $request->merge(['address' => $sessionCheckoutData]);
        }

        OrderHelper::setOrderSessionData($token, $sessionCheckoutData);

        $orders->loadMissing([
            'products',
            'products.product',
            'products.product.variationInfo',
            'products.product.variationInfo.configurableProduct',
            'products.product.variationInfo.configurableProduct.tax',
        ]);

        Cart::instance('cart')->destroy();
        foreach ($orders as $order) {
            foreach ($order->products as $orderProduct) {
                $request->merge(['qty' => $orderProduct->qty]);

                $product = $orderProduct->product;
                if ($product) {
                    OrderHelper::handleAddCart($product, $request);
                }
            }
        }

        /**
         * @var EloquentCollection $products
         */
        $products = Cart::instance('cart')->products();
        if ($products->count()) {
            $this->handleProcessOrder($products, $token, $sessionCheckoutData, $request);
        }

        return $this->app->make(BaseHttpResponse::class)
            ->setNextUrl(route('public.checkout.information', $token))
            ->setMessage(__('You have recovered from previous orders!'));
    }

    public function handleProcessOrder(
        EloquentCollection $products,
        string $token,
        array $sessionData,
        Request $request
    ): array {
        $groupedProducts = $this->cartGroupByStore($products);

        $currentUserId = 0;
        if (auth('customer')->check()) {
            $currentUserId = auth('customer')->id();
        }
        $preOrders = collect();
        $mpSessionData = Arr::get($sessionData, 'marketplace', []);

        $orderIds = collect($mpSessionData ?: [])->pluck('created_order_id');
        if ($orderIds) {
            $preOrders = Order::query()->whereIn('id', $orderIds)->with('address')->get();
        }

        $foundOrderIds = [];

        $addressKeys = [
            'name',
            'phone',
            'email',
            'country',
            'state',
            'city',
            'address',
            'zip_code',
            'address_id',
            'billing_address_same_as_shipping_address',
            'billing_address',
        ];
        $addressData = Arr::only($sessionData, $addressKeys);

        foreach ($groupedProducts as $key => $productsByStore) {
            $sessionDataInStore = Arr::get($mpSessionData, $key, []);
            $order = $preOrders->firstWhere('store_id', $key);
            if ($order) {
                $foundOrderIds[] = $key;
            }

            $sessionDataInStore = array_merge($sessionDataInStore, $addressData);
            $mpSessionData[$key] = $this->handleOrderStore(
                $productsByStore,
                $token,
                $sessionDataInStore,
                $request,
                $currentUserId,
                $order
            );
        }

        // Remove orders not exists pre checkout
        if ($preOrders) {
            foreach ($preOrders as $order) {
                if (! in_array($order->store_id, $foundOrderIds)) {
                    $order->delete();
                }
            }
        }

        $sessionData = array_merge($sessionData, ['marketplace' => $mpSessionData]);

        OrderHelper::setOrderSessionData($token, $sessionData);

        return $sessionData;
    }

    public function handleOrderStore(
        array|Collection $products,
        string $token,
        array $sessionData,
        Request $request,
        int|string|null $currentUserId,
        ?Order $order
    ): array {
        $store = $products['store'];
        $cartItems = $products['products']->pluck('cartItem');

        $generalData = [
            'user_id' => $currentUserId,
            'shipping_method' => $request->input('shipping_method.' . $store->id, ShippingMethodEnum::DEFAULT),
            'shipping_option' => $request->input('shipping_option.' . $store->id),
            'coupon_code' => Arr::get($sessionData, 'applied_coupon_code'),
            'token' => $token,
        ];

        [$sessionData, $order] = OrderHelper::processOrderInCheckout(
            $sessionData,
            $request,
            $cartItems,
            $order,
            $generalData
        );

        Arr::set(
            $sessionData,
            'is_save_order_shipping_address',
            EcommerceHelper::isSaveOrderShippingAddress($products['products'])
        );

        Arr::set($sessionData, 'created_order_id', $order->id);
        $sessionData = OrderHelper::processAddressOrder($currentUserId, $sessionData, $request);

        $order->store_id = $store->id;
        $order->save();

        return OrderHelper::processOrderProductData($products, $sessionData);
    }

    public function processCheckoutRulesRequest(array $rules): array
    {
        unset($rules['shipping_method']);
        [$stores, $groupedProducts] = $this->getStoresInCart(true);
        foreach ($stores as $storeId => $storeName) {
            $products = collect($groupedProducts[$storeId]);
            if (EcommerceHelper::isAvailableShipping($products)) {
                $rules["shipping_method.$storeId"] = 'required|' . Rule::in(ShippingMethodEnum::values());
                $rules["shipping_option.$storeId"] = 'required';
            }
        }

        return $rules;
    }

    protected function getStoresInCart($includeProducts = false): array
    {
        $originalProducts = Cart::instance('cart')->products()->pluck('original_product');
        $storeIdsInCart = $originalProducts->pluck('store_id');
        $stores = Store::query()->whereIn('id', $storeIdsInCart)->get();
        $storesInCart = [];
        $groupedProducts = [];
        foreach ($originalProducts as $original) {
            if ($original->store_id) {
                if ($store = $stores->firstWhere('id', $original->store_id)) {
                    $storesInCart[$store->id] = $store->name;
                    $groupedProducts[$store->id][] = $original;

                    continue;
                }
            }
            $groupedProducts[0][] = $original;
            $storesInCart[0] = theme_option('site_title');
        }
        if ($includeProducts) {
            return [$storesInCart, $groupedProducts];
        }

        return $storesInCart;
    }

    public function processCheckoutMessagesRequest(array $messages): array
    {
        $stores = $this->getStoresInCart();
        foreach ($stores as $storeId => $storeName) {
            $messages["shipping_method.$storeId.required"] = trans(
                'plugins/marketplace::order.shipping_method_required',
                ['name' => $storeName]
            );
            $messages["shipping_method.$storeId.in"] = trans(
                'plugins/marketplace::order.shipping_method_in',
                ['name' => $storeName]
            );
            $messages["shipping_option.$storeId.required"] = trans(
                'plugins/marketplace::order.shipping_option_required',
                ['name' => $storeName]
            );
        }

        return $messages;
    }

    public function afterOrderStatusCompleted(OrderModel $order)
    {
        $order->loadMissing(['store', 'store.customer']);

        if ($order->store->id && $order->store->customer->id) {
            $customer = $order->store->customer;
            $vendorInfo = $customer->vendorInfo;
            if (! $vendorInfo->id) {
                $vendorInfo = VendorInfo::query()
                    ->create([
                        'customer_id' => $customer->id,
                    ]);
            }

            if ($vendorInfo->id) {
                $revenue = Revenue::query()->where('order_id', $order->getKey())->first();
                $orderAmountWithoutShippingFee = $order->amount - $order->shipping_amount - $order->tax_amount;
                if (! MarketplaceHelper::isCommissionCategoryFeeBasedEnabled()) {
                    $feePercentage = MarketplaceHelper::getSetting('fee_per_order', 0);
                    $fee = $orderAmountWithoutShippingFee * ($feePercentage / 100);
                } else {
                    $fee = $this->calculatorCommissionFeeByProduct($order->products);
                }
                $amount = $orderAmountWithoutShippingFee - $fee;
                $currentBalance = $customer->balance;

                $amountByCurrency = $amount;
                $revenueAmount = $revenue ? $revenue->amount : 0;

                $data = [
                    'sub_amount' => $orderAmountWithoutShippingFee,
                    'fee' => $fee,
                    'amount' => $amount,
                    'currency' => get_application_currency()->title,
                    'current_balance' => $currentBalance,
                    'customer_id' => $customer->getKey(),
                ];

                try {
                    DB::beginTransaction();

                    if ($revenue) {
                        $amountByCurrency -= $revenueAmount;
                        $fee = 0;
                        $data['current_balance'] = $currentBalance - $revenueAmount;
                        $revenue->fill($data);
                        $revenue->save();
                    } else {
                        Revenue::query()->create(
                            array_merge([
                                'order_id' => $order->getKey(),
                            ], $data)
                        );

                        $vendorInfo->total_revenue += $amountByCurrency;
                    }
                    $vendorInfo->balance += $amountByCurrency;
                    $vendorInfo->total_fee += $fee;
                    $vendorInfo->save();

                    DB::commit();
                } catch (Throwable|Exception $th) {
                    DB::rollBack();

                    return (new BaseHttpResponse())
                        ->setError()
                        ->setMessage($th->getMessage());
                }
            }
        }

        return $order;
    }

    protected function calculatorCommissionFeeByProduct(Collection $orderProducts): float|int
    {
        $totalFee = 0;
        foreach ($orderProducts as $orderProduct) {
            $product = $orderProduct->product->original_product;

            if (! $product) {
                continue;
            }

            $listCategories = $product->categories()->pluck('category_id')->all();

            $commissionFeePercentage = MarketplaceHelper::getSetting('fee_per_order', 0);
            $commissionSetting = CategoryCommission::query()
                ->whereIn('product_category_id', $listCategories)
                ->orderBy('commission_percentage', 'desc')
                ->value('commission_percentage');

            if ($commissionSetting) {
                $commissionFeePercentage = $commissionSetting;
            }

            $totalFee += $orderProduct->price * $commissionFeePercentage / 100;
        }

        return $totalFee;
    }

    public function afterReturnOrderCompleted(OrderReturn $orderReturn)
    {
        $order = $orderReturn->order;
        if ($order && $order->store->id && $order->store->customer->id) {
            $customer = $order->store->customer;
            $vendorInfo = $customer->vendorInfo;
            if (! $vendorInfo->id) {
                $vendorInfo = VendorInfo::query()
                    ->create([
                        'customer_id' => $customer->id,
                    ]);
            }

            if ($vendorInfo->id) {
                $refundAmount = $orderReturn->items->sum('refund_amount');
                if (! MarketplaceHelper::isCommissionCategoryFeeBasedEnabled()) {
                    $feePercentage = MarketplaceHelper::getSetting('fee_per_order', 0);
                    $fee = $refundAmount * ($feePercentage / 100);
                } else {
                    $products = $orderReturn->items->map(fn ($item) => $item->product);
                    $fee = $this->calculatorCommissionFeeByProduct($products);
                }
                $fee = $fee * -1;
                $refundAmount = $refundAmount * -1;
                $amount = $refundAmount - $fee;
                $currentBalance = $customer->balance;

                $data = [
                    'sub_amount' => $refundAmount,
                    'fee' => $fee,
                    'amount' => $amount,
                    'currency' => get_application_currency()->title,
                    'current_balance' => $currentBalance,
                    'customer_id' => $customer->getKey(),
                    'order_id' => $order->id,
                    'type' => RevenueTypeEnum::ORDER_RETURN,
                    'description' => trans('plugins/marketplace::order.return.description', [
                        'order' => $order->code,
                    ]),
                ];

                Revenue::query()->create($data);

                $vendorInfo->total_revenue += $amount;
                $vendorInfo->balance += $amount;
                $vendorInfo->total_fee += $fee;
                $vendorInfo->save();
            }
        }
    }
}
