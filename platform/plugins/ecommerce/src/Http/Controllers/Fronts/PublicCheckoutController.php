<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Requests\ApplyCouponRequest;
use Botble\Ecommerce\Http\Requests\CheckoutRequest;
use Botble\Ecommerce\Http\Requests\SaveCheckoutInformationRequest;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Services\Footprints\FootprinterInterface;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleRemoveCouponService;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Optimize\Facades\OptimizerHelper;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PublicCheckoutController
{
    public function __construct()
    {
        OptimizerHelper::disable();
    }

    public function getCheckout(
        string $token,
        Request $request,
        BaseHttpResponse $response,
        HandleShippingFeeService $shippingFeeService,
        HandleApplyCouponService $applyCouponService,
        HandleRemoveCouponService $removeCouponService,
        HandleApplyPromotionsService $applyPromotionsService
    ) {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        if (! EcommerceHelper::isEnabledGuestCheckout() && ! auth('customer')->check()) {
            return $response->setNextUrl(route('customer.login'));
        }

        if ($token !== session('tracked_start_checkout')) {
            $order = Order::query()->where(['token' => $token, 'is_finished' => false])->first();

            if (! $order) {
                return $response->setNextUrl(route('public.index'));
            }
        }

        if (
            ! $request->session()->has('error_msg') &&
            $request->input('error') == 1 &&
            $request->input('error_type') == 'payment'
        ) {
            $request->session()->flash('error_msg', __('Payment failed!'));
        }

        $sessionCheckoutData = OrderHelper::getOrderSessionData($token);

        $products = Cart::instance('cart')->products();
        if (! $products->count()) {
            return $response->setNextUrl(route('public.cart'));
        }

        foreach ($products as $product) {
            if ($product->isOutOfStock()) {
                return $response
                    ->setError()
                    ->setNextUrl(route('public.cart'))
                    ->setMessage(
                        __('Product :product is out of stock!', ['product' => $product->original_product->name])
                    );
            }
        }

        if (EcommerceHelper::isEnabledSupportDigitalProducts() && ! EcommerceHelper::canCheckoutForDigitalProducts(
            $products
        )) {
            return $response
                ->setError()
                ->setNextUrl(route('customer.login'))
                ->setMessage(__('Your shopping cart has digital product(s), so you need to sign in to continue!'));
        }

        $sessionCheckoutData = $this->processOrderData($token, $sessionCheckoutData, $request);

        $paymentMethod = null;

        if (is_plugin_active('payment')) {
            $paymentMethod = $request->input(
                'payment_method',
                session('selected_payment_method') ?: PaymentHelper::defaultPaymentMethod()
            );
        }

        if ($paymentMethod) {
            session()->put('selected_payment_method', $paymentMethod);
        }

        if (is_plugin_active('marketplace')) {
            [
                $sessionCheckoutData,
                $shipping,
                $defaultShippingMethod,
                $defaultShippingOption,
                $shippingAmount,
                $promotionDiscountAmount,
                $couponDiscountAmount,
            ] = apply_filters(PROCESS_CHECKOUT_ORDER_DATA_ECOMMERCE, $products, $token, $sessionCheckoutData, $request);
        } else {
            $promotionDiscountAmount = $applyPromotionsService->execute($token);

            $sessionCheckoutData['promotion_discount_amount'] = $promotionDiscountAmount;

            $couponDiscountAmount = 0;
            if (session()->has('applied_coupon_code')) {
                $couponDiscountAmount = Arr::get($sessionCheckoutData, 'coupon_discount_amount', 0);
            }

            $orderTotal = Cart::instance('cart')->rawTotal() - $promotionDiscountAmount - $couponDiscountAmount;
            $orderTotal = max($orderTotal, 0);

            $isAvailableShipping = EcommerceHelper::isAvailableShipping($products);

            $shipping = [];
            $defaultShippingMethod = $request->input(
                'shipping_method',
                Arr::get($sessionCheckoutData, 'shipping_method', ShippingMethodEnum::DEFAULT)
            );
            $defaultShippingOption = $request->input(
                'shipping_option',
                Arr::get($sessionCheckoutData, 'shipping_option')
            );
            $shippingAmount = 0;

            if ($isAvailableShipping) {
                $origin = EcommerceHelper::getOriginAddress();
                $shippingData = EcommerceHelper::getShippingData(
                    $products,
                    $sessionCheckoutData,
                    $origin,
                    $orderTotal,
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

                if ($shipping) {
                    if (! $defaultShippingMethod) {
                        $defaultShippingMethod = old(
                            'shipping_method',
                            Arr::get($sessionCheckoutData, 'shipping_method', Arr::first(array_keys($shipping)))
                        );
                    }
                    if (! $defaultShippingOption) {
                        $defaultShippingOption = old(
                            'shipping_option',
                            Arr::get($sessionCheckoutData, 'shipping_option', $defaultShippingOption)
                        );
                    }
                }

                $shippingAmount = Arr::get(
                    $shipping,
                    $defaultShippingMethod . '.' . $defaultShippingOption . '.price',
                    0
                );

                Arr::set($sessionCheckoutData, 'shipping_method', $defaultShippingMethod);
                Arr::set($sessionCheckoutData, 'shipping_option', $defaultShippingOption);
                Arr::set($sessionCheckoutData, 'shipping_amount', $shippingAmount);

                OrderHelper::setOrderSessionData($token, $sessionCheckoutData);
            }

            if (session()->has('applied_coupon_code')) {
                if (! $request->input('applied_coupon')) {
                    $discount = $applyCouponService->getCouponData(
                        session('applied_coupon_code'),
                        $sessionCheckoutData
                    );
                    if (empty($discount)) {
                        $removeCouponService->execute();
                    } else {
                        $shippingAmount = Arr::get($sessionCheckoutData, 'is_free_shipping') ? 0 : $shippingAmount;
                    }
                } else {
                    $shippingAmount = Arr::get($sessionCheckoutData, 'is_free_shipping') ? 0 : $shippingAmount;
                }
            }

            $sessionCheckoutData['is_available_shipping'] = $isAvailableShipping;

            if (! $sessionCheckoutData['is_available_shipping']) {
                $shippingAmount = 0;
            }
        }

        $isShowAddressForm = EcommerceHelper::isSaveOrderShippingAddress($products);

        $data = compact(
            'token',
            'shipping',
            'defaultShippingMethod',
            'defaultShippingOption',
            'shippingAmount',
            'promotionDiscountAmount',
            'couponDiscountAmount',
            'sessionCheckoutData',
            'products',
            'isShowAddressForm',
        );

        if (auth('customer')->check()) {
            $addresses = auth('customer')->user()->addresses;
            $isAvailableAddress = ! $addresses->isEmpty();

            if (Arr::get($sessionCheckoutData, 'is_new_address')) {
                $sessionAddressId = 'new';
            } else {
                $sessionAddressId = Arr::get(
                    $sessionCheckoutData,
                    'address_id',
                    $isAvailableAddress ? $addresses->first()->id : null
                );
                if (! $sessionAddressId && $isAvailableAddress) {
                    $address = $addresses->firstWhere('is_default') ?: $addresses->first();
                    $sessionAddressId = $address->id;
                }
            }

            $data = array_merge($data, compact('addresses', 'isAvailableAddress', 'sessionAddressId'));
        }

        $checkoutView = Theme::getThemeNamespace('views.ecommerce.orders.checkout');

        if (view()->exists($checkoutView)) {
            return view($checkoutView, $data);
        }

        return view('plugins/ecommerce::orders.checkout', $data);
    }

    protected function processOrderData(
        string $token,
        array $sessionData,
        Request $request,
        bool $finished = false
    ): array {
        if ($request->has('billing_address_same_as_shipping_address')) {
            $sessionData['billing_address_same_as_shipping_address'] = $request->input(
                'billing_address_same_as_shipping_address'
            );
        }

        if ($request->has('billing_address')) {
            $sessionData['billing_address'] = $request->input('billing_address');
        }

        if ($request->has('address.address_id')) {
            $sessionData['is_new_address'] = $request->input('address.address_id') == 'new';
        }

        if ($request->input('address', [])) {
            if (! isset($sessionData['created_account']) && $request->input('create_account') == 1) {
                $validator = Validator::make($request->input(), [
                    'password' => 'required|min:6',
                    'password_confirmation' => 'required|same:password',
                    'address.email' => 'required|max:60|min:6|email|unique:ec_customers,email',
                    'address.name' => 'required|min:3|max:120',
                ]);

                if (! $validator->fails()) {
                    $customer = Customer::query()->create([
                        'name' => BaseHelper::clean($request->input('address.name')),
                        'email' => BaseHelper::clean($request->input('address.email')),
                        'phone' => BaseHelper::clean($request->input('address.phone')),
                        'password' => Hash::make($request->input('password')),
                    ]);

                    auth('customer')->attempt([
                        'email' => $request->input('address.email'),
                        'password' => $request->input('password'),
                    ], true);

                    event(new Registered($customer));

                    $sessionData['created_account'] = true;

                    $address = Address::query()
                        ->create(
                            array_merge($request->input('address'), [
                                'customer_id' => $customer->id,
                                'is_default' => true,
                            ])
                        );

                    $request->merge(['address.address_id' => $address->id]);
                    $sessionData['address_id'] = $address->id;
                }
            }

            if ($finished && auth('customer')->check()) {
                $customer = auth('customer')->user();
                if ($customer->addresses->count() == 0 || $request->input('address.address_id') == 'new') {
                    $address = Address::query()
                        ->create(
                            array_merge($request->input('address', []), [
                                'customer_id' => auth('customer')->id(),
                                'is_default' => $customer->addresses->count() == 0,
                            ])
                        );

                    $request->merge(['address.address_id' => $address->id]);
                    $sessionData['address_id'] = $address->id;
                }
            }
        }

        $address = null;

        if (($addressId = $request->input('address.address_id')) && $addressId !== 'new') {
            $address = Address::query()->find($addressId);
            if ($address) {
                $sessionData['address_id'] = $address->getKey();
            }
        } elseif (auth('customer')->check() && ! Arr::get($sessionData, 'address_id')) {
            $address = Address::query()->where([
                'customer_id' => auth('customer')->id(),
                'is_default' => true,
            ])->first();

            if ($address) {
                $sessionData['address_id'] = $address->id;
            }
        }

        $addressData = [
            'billing_address_same_as_shipping_address' => Arr::get(
                $sessionData,
                'billing_address_same_as_shipping_address',
                true
            ),
            'billing_address' => Arr::get($sessionData, 'billing_address', []),
        ];

        if (! empty($address)) {
            $addressData = [
                'name' => $address->name,
                'phone' => $address->phone,
                'email' => $address->email,
                'country' => $address->country,
                'state' => $address->state,
                'city' => $address->city,
                'address' => $address->address,
                'zip_code' => $address->zip_code,
                'address_id' => $address->id,
            ];
        } elseif ($addressFromInput = (array)$request->input('address', [])) {
            $addressData = $addressFromInput;
        }

        $addressData = OrderHelper::cleanData($addressData);

        $sessionData = array_merge($sessionData, $addressData);

        $products = Cart::instance('cart')->products();
        if (is_plugin_active('marketplace')) {
            $sessionData = apply_filters(
                HANDLE_PROCESS_ORDER_DATA_ECOMMERCE,
                $products,
                $token,
                $sessionData,
                $request
            );

            OrderHelper::setOrderSessionData($token, $sessionData);

            return $sessionData;
        }

        if (! isset($sessionData['created_order'])) {
            $currentUserId = 0;
            if (auth('customer')->check()) {
                $currentUserId = auth('customer')->id();
            }

            $request->merge([
                'amount' => Cart::instance('cart')->rawTotal(),
                'user_id' => $currentUserId,
                'shipping_method' => $request->input('shipping_method', ShippingMethodEnum::DEFAULT),
                'shipping_option' => $request->input('shipping_option'),
                'shipping_amount' => 0,
                'tax_amount' => Cart::instance('cart')->rawTax(),
                'sub_total' => Cart::instance('cart')->rawSubTotal(),
                'coupon_code' => session('applied_coupon_code'),
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'is_finished' => false,
                'token' => $token,
            ]);

            $order = Order::query()->where(compact('token'))->first();

            $order = $this->createOrderFromData($request->input(), $order);

            $sessionData['created_order'] = true;
            $sessionData['created_order_id'] = $order->getKey();
        }

        if (! empty($address)) {
            $addressData['order_id'] = $sessionData['created_order_id'];
        } elseif ((array)$request->input('address', [])) {
            $addressData = array_merge(
                ['order_id' => $sessionData['created_order_id']],
                (array)$request->input('address', [])
            );
        }

        $sessionData['is_save_order_shipping_address'] = EcommerceHelper::isSaveOrderShippingAddress($products);

        $sessionData = OrderHelper::checkAndCreateOrderAddress($addressData, $sessionData);

        if (! isset($sessionData['created_order_product'])) {
            $weight = 0;
            foreach (Cart::instance('cart')->content() as $cartItem) {
                $product = Product::query()->find($cartItem->id);
                if ($product && $product->weight) {
                    $weight += $product->weight * $cartItem->qty;
                }
            }

            $weight = EcommerceHelper::validateOrderWeight($weight);

            OrderProduct::query()->where(['order_id' => $sessionData['created_order_id']])->delete();

            foreach (Cart::instance('cart')->content() as $cartItem) {
                $product = Product::query()->find($cartItem->id);

                $data = [
                    'order_id' => $sessionData['created_order_id'],
                    'product_id' => $cartItem->id,
                    'product_name' => $cartItem->name,
                    'product_image' => $product->original_product->image,
                    'qty' => $cartItem->qty,
                    'weight' => $weight,
                    'price' => $cartItem->price,
                    'tax_amount' => $cartItem->tax,
                    'options' => $cartItem->options,
                    'product_type' => $product?->product_type,
                ];

                if ($cartItem->options['options']) {
                    $data['product_options'] = $cartItem->options['options'];
                }

                OrderProduct::query()->create($data);
            }

            $sessionData['created_order_product'] = Cart::instance('cart')->getLastUpdatedAt();
        }

        OrderHelper::setOrderSessionData($token, $sessionData);

        return $sessionData;
    }

    public function postSaveInformation(
        string $token,
        SaveCheckoutInformationRequest $request,
        BaseHttpResponse $response,
        HandleApplyCouponService $applyCouponService,
        HandleRemoveCouponService $removeCouponService
    ) {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        if ($token !== session('tracked_start_checkout')) {
            $order = Order::query()->where(['token' => $token, 'is_finished' => false])->first();

            if (! $order) {
                return $response->setNextUrl(route('public.index'));
            }
        }

        if ($paymentMethod = $request->input('payment_method')) {
            session()->put('selected_payment_method', $paymentMethod);
        }

        if (is_plugin_active('marketplace')) {
            $sessionData = array_merge(OrderHelper::getOrderSessionData($token), $request->input('address'));

            $sessionData = apply_filters(
                PROCESS_POST_SAVE_INFORMATION_CHECKOUT_ECOMMERCE,
                $sessionData,
                $request,
                $token
            );
        } else {
            $sessionData = array_merge(OrderHelper::getOrderSessionData($token), $request->input('address'));
            OrderHelper::setOrderSessionData($token, $sessionData);
            if (session()->has('applied_coupon_code')) {
                $discount = $applyCouponService->getCouponData(session('applied_coupon_code'), $sessionData);
                if (! $discount) {
                    $removeCouponService->execute();
                }
            }
        }

        $sessionData = $this->processOrderData($token, $sessionData, $request);

        return $response->setData($sessionData);
    }

    public function postCheckout(
        string $token,
        CheckoutRequest $request,
        BaseHttpResponse $response,
        HandleShippingFeeService $shippingFeeService,
        HandleApplyCouponService $applyCouponService,
        HandleRemoveCouponService $removeCouponService,
        HandleApplyPromotionsService $handleApplyPromotionsService
    ) {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        if (! EcommerceHelper::isEnabledGuestCheckout() && ! auth('customer')->check()) {
            return $response->setNextUrl(route('customer.login'));
        }

        if (! Cart::instance('cart')->count()) {
            return $response
                ->setError()
                ->setMessage(__('No products in cart'));
        }

        $products = Cart::instance('cart')->products();

        if (EcommerceHelper::isEnabledSupportDigitalProducts() && ! EcommerceHelper::canCheckoutForDigitalProducts(
            $products
        )) {
            return $response
                ->setError()
                ->setNextUrl(route('customer.login'))
                ->setMessage(__('Your shopping cart has digital product(s), so you need to sign in to continue!'));
        }

        if (EcommerceHelper::getMinimumOrderAmount() > Cart::instance('cart')->rawSubTotal()) {
            return $response
                ->setError()
                ->setMessage(
                    __('Minimum order amount is :amount, you need to buy more :more to place an order!', [
                        'amount' => format_price(EcommerceHelper::getMinimumOrderAmount()),
                        'more' => format_price(
                            EcommerceHelper::getMinimumOrderAmount() - Cart::instance('cart')
                                ->rawSubTotal()
                        ),
                    ])
                );
        }

        $sessionData = OrderHelper::getOrderSessionData($token);

        $sessionData = $this->processOrderData($token, $sessionData, $request, true);

        foreach ($products as $product) {
            if ($product->isOutOfStock()) {
                return $response
                    ->setError()
                    ->setMessage(
                        __('Product :product is out of stock!', ['product' => $product->original_product->name])
                    );
            }
        }

        $paymentMethod = $request->input('payment_method', session('selected_payment_method'));
        if ($paymentMethod) {
            session()->put('selected_payment_method', $paymentMethod);
        }

        do_action('ecommerce_post_checkout', $products, $request, $token, $sessionData);

        if (is_plugin_active('marketplace')) {
            return apply_filters(
                HANDLE_PROCESS_POST_CHECKOUT_ORDER_DATA_ECOMMERCE,
                $products,
                $request,
                $token,
                $sessionData,
                $response
            );
        }

        $isAvailableShipping = EcommerceHelper::isAvailableShipping($products);

        $shippingMethodInput = $request->input('shipping_method', ShippingMethodEnum::DEFAULT);

        $promotionDiscountAmount = $handleApplyPromotionsService->execute($token);
        $couponDiscountAmount = Arr::get($sessionData, 'coupon_discount_amount');
        $rawTotal = Cart::instance('cart')->rawTotal();
        $orderAmount = max($rawTotal - $promotionDiscountAmount - $couponDiscountAmount, 0);

        $shippingAmount = 0;

        $shippingData = [];
        if ($isAvailableShipping) {
            $origin = EcommerceHelper::getOriginAddress();
            $shippingData = EcommerceHelper::getShippingData(
                $products,
                $sessionData,
                $origin,
                $orderAmount,
                $paymentMethod
            );

            $shippingMethodData = $shippingFeeService->execute(
                $shippingData,
                $shippingMethodInput,
                $request->input('shipping_option')
            );

            $shippingMethod = Arr::first($shippingMethodData);
            if (! $shippingMethod) {
                throw ValidationException::withMessages([
                    'shipping_method' => trans(
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

        if (session()->has('applied_coupon_code')) {
            $discount = $applyCouponService->getCouponData(session('applied_coupon_code'), $sessionData);
            if (empty($discount)) {
                $removeCouponService->execute();
            } else {
                $shippingAmount = Arr::get($sessionData, 'is_free_shipping') ? 0 : $shippingAmount;
            }
        }

        $currentUserId = 0;
        if (auth('customer')->check()) {
            $currentUserId = auth('customer')->id();
        }

        $orderAmount += (float)$shippingAmount;

        $request->merge([
            'amount' => $orderAmount ?: 0,
            'currency' => $request->input('currency', strtoupper(get_application_currency()->title)),
            'user_id' => $currentUserId,
            'shipping_method' => $isAvailableShipping ? $shippingMethodInput : '',
            'shipping_option' => $isAvailableShipping ? $request->input('shipping_option') : null,
            'shipping_amount' => (float)$shippingAmount,
            'tax_amount' => Cart::instance('cart')->rawTax(),
            'sub_total' => Cart::instance('cart')->rawSubTotal(),
            'coupon_code' => session('applied_coupon_code'),
            'discount_amount' => $promotionDiscountAmount + $couponDiscountAmount,
            'status' => OrderStatusEnum::PENDING,
            'token' => $token,
        ]);

        $order = Order::query()->where(compact('token'))->first();

        $order = $this->createOrderFromData($request->input(), $order);

        OrderHistory::query()->create([
            'action' => 'create_order_from_payment_page',
            'description' => __('Order was created from checkout page'),
            'order_id' => $order->getKey(),
        ]);

        if ($isAvailableShipping) {
            Shipment::query()->create([
                'order_id' => $order->getKey(),
                'user_id' => 0,
                'weight' => $shippingData ? Arr::get($shippingData, 'weight') : 0,
                'cod_amount' => (is_plugin_active(
                    'payment'
                ) && $order->payment->id && $order->payment->status != PaymentStatusEnum::COMPLETED) ? $order->amount : 0,
                'cod_status' => ShippingCodStatusEnum::PENDING,
                'type' => $order->shipping_method,
                'status' => ShippingStatusEnum::PENDING,
                'price' => $order->shipping_amount,
                'rate_id' => $shippingData ? Arr::get($shippingMethod, 'id', '') : '',
                'shipment_id' => $shippingData ? Arr::get($shippingMethod, 'shipment_id', '') : '',
                'shipping_company_name' => $shippingData ? Arr::get($shippingMethod, 'company_name', '') : '',
            ]);
        }

        if (
            EcommerceHelper::isDisplayTaxFieldsAtCheckoutPage()
            && $request->boolean('with_tax_information')
        ) {
            $order->taxInformation()->create($request->input('tax_information'));
        }

        if ($appliedCouponCode = session('applied_coupon_code')) {
            Discount::getFacadeRoot()->afterOrderPlaced($appliedCouponCode);
        }

        OrderProduct::query()->where(['order_id' => $order->getKey()])->delete();

        foreach (Cart::instance('cart')->content() as $cartItem) {
            $product = Product::query()->find($cartItem->id);

            $data = [
                'order_id' => $order->getKey(),
                'product_id' => $cartItem->id,
                'product_name' => $cartItem->name,
                'product_image' => $product->original_product->image,
                'qty' => $cartItem->qty,
                'weight' => Arr::get($cartItem->options, 'weight', 0),
                'price' => $cartItem->price,
                'tax_amount' => $cartItem->tax,
                'options' => $cartItem->options,
                'product_type' => $product?->product_type,
            ];

            if ($cartItem->options['options']) {
                $data['product_options'] = $cartItem->options['options'];
            }

            OrderProduct::query()->create($data);
        }

        $request->merge([
            'order_id' => $order->getKey(),
        ]);

        do_action('ecommerce_before_processing_payment', $products, $request, $token, $sessionData);

        if (! is_plugin_active('payment')) {
            return redirect()->to(route('public.checkout.success', OrderHelper::getOrderSessionToken()));
        }

        $paymentData = [
            'error' => false,
            'message' => false,
            'amount' => (float)format_price($order->amount, null, true),
            'currency' => strtoupper(get_application_currency()->title),
            'type' => $request->input('payment_method'),
            'charge_id' => null,
        ];

        $paymentData = apply_filters(FILTER_ECOMMERCE_PROCESS_PAYMENT, $paymentData, $request);

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

    public function getCheckoutSuccess(string $token, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        $order = Order::query()
            ->where('token', $token)
            ->with(['address', 'products', 'taxInformation'])
            ->orderBy('id', 'desc')
            ->first();

        if (! $order) {
            abort(404);
        }

        if (is_plugin_active('payment') && ! $order->payment_id) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->setMessage(__('Payment failed!'));
        }

        if (is_plugin_active('marketplace')) {
            return apply_filters(PROCESS_GET_CHECKOUT_SUCCESS_IN_ORDER, $token, $response);
        }

        OrderHelper::clearSessions($token);

        $products = collect();

        $productsIds = $order->products->pluck('product_id')->all();

        if (! empty($productsIds)) {
            $products = get_products([
                'condition' => [
                    ['ec_products.id', 'IN', $productsIds],
                ],
                'select' => [
                    'ec_products.id',
                    'ec_products.images',
                    'ec_products.name',
                    'ec_products.price',
                    'ec_products.sale_price',
                    'ec_products.sale_type',
                    'ec_products.start_date',
                    'ec_products.end_date',
                    'ec_products.sku',
                    'ec_products.order',
                    'ec_products.created_at',
                    'ec_products.is_variation',
                ],
                'with' => [
                    'variationProductAttributes',
                ],
            ]);
        }

        return view('plugins/ecommerce::orders.thank-you', compact('order', 'products'));
    }

    public function postApplyCoupon(
        ApplyCouponRequest $request,
        HandleApplyCouponService $handleApplyCouponService,
        BaseHttpResponse $response
    ) {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }
        $result = [
            'error' => false,
            'message' => '',
        ];
        if (is_plugin_active('marketplace')) {
            $result = apply_filters(HANDLE_POST_APPLY_COUPON_CODE_ECOMMERCE, $result, $request);
        } else {
            $result = $handleApplyCouponService->execute($request->input('coupon_code'));
        }

        if ($result['error']) {
            return $response
                ->setError()
                ->withInput()
                ->setMessage($result['message']);
        }

        $couponCode = $request->input('coupon_code');

        return $response
            ->setMessage(__('Applied coupon ":code" successfully!', ['code' => $couponCode]));
    }

    public function postRemoveCoupon(
        Request $request,
        HandleRemoveCouponService $removeCouponService,
        BaseHttpResponse $response
    ) {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        if (is_plugin_active('marketplace')) {
            $products = Cart::instance('cart')->products();
            $result = apply_filters(HANDLE_POST_REMOVE_COUPON_CODE_ECOMMERCE, $products, $request);
        } else {
            $result = $removeCouponService->execute();
        }

        if ($result['error']) {
            if ($request->ajax()) {
                return $result;
            }

            return $response
                ->setError()
                ->setData($result)
                ->setMessage($result['message']);
        }

        return $response
            ->setMessage(__('Removed coupon :code successfully!', ['code' => session('applied_coupon_code')]));
    }

    public function getCheckoutRecover(string $token, Request $request, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isCartEnabled()) {
            abort(404);
        }

        if (! EcommerceHelper::isEnabledGuestCheckout() && ! auth('customer')->check()) {
            return $response->setNextUrl(route('customer.login'));
        }

        if (is_plugin_active('marketplace')) {
            return apply_filters(PROCESS_GET_CHECKOUT_RECOVER_ECOMMERCE, $token, $request);
        }

        $order = Order::query()
            ->where([
                'token' => $token,
                'is_finished' => false,
            ])
            ->with(['products', 'address'])
            ->first();

        if (! $order) {
            abort(404);
        }

        if (session()->has('tracked_start_checkout') && session('tracked_start_checkout') == $token) {
            $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
        } else {
            session(['tracked_start_checkout' => $token]);
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
        }

        Cart::instance('cart')->destroy();
        foreach ($order->products as $orderProduct) {
            $request->merge(['qty' => $orderProduct->qty]);

            $product = Product::query()->find($orderProduct->product_id);
            if ($product) {
                OrderHelper::handleAddCart($product, $request);
            }
        }

        OrderHelper::setOrderSessionData($token, $sessionCheckoutData);

        return $response->setNextUrl(route('public.checkout.information', $token))
            ->setMessage(__('You have recovered from previous orders!'));
    }

    protected function createOrderFromData(array $data, ?Order $order): Order|null|false
    {
        $data['is_finished'] = false;

        if ($order) {
            $order->fill($data);
            $order->save();
        } else {
            $order = Order::query()->create($data);
        }

        if (! $order->referral()->count()) {
            $referrals = app(FootprinterInterface::class)->getFootprints();

            if ($referrals) {
                $order->referral()->create($referrals);
            }
        }

        do_action('ecommerce_create_order_from_data', $data, $order);

        return $order;
    }
}
