<?php

namespace Botble\Ecommerce\Supports;

use Barryvdh\DomPDF\PDF as PDFHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\Html;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\EcommerceHelper as EcommerceHelperFacade;
use Botble\Ecommerce\Facades\InvoiceHelper as InvoiceHelperFacade;
use Botble\Ecommerce\Http\Requests\CheckoutRequest;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\OptionValue;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class OrderHelper
{
    public function processOrder(string|array|null $orderIds, string|null $chargeId = null): bool|Collection|array|Model
    {
        $orderIds = (array)$orderIds;

        $orders = Order::query()->whereIn('id', $orderIds)->get();

        if (! $orders->count()) {
            return false;
        }

        if (is_plugin_active('payment') && $chargeId) {
            $payments = Payment::query()
                ->where('charge_id', $chargeId)
                ->whereIn('order_id', $orderIds)
                ->get();

            if ($payments->count()) {
                foreach ($orders as $order) {
                    $payment = $payments->firstWhere('order_id', $order->getKey());
                    if ($payment) {
                        $order->payment_id = $payment->getKey();
                        $order->save();
                    }
                }
            }
        }

        foreach ($orders as $order) {
            if (! $order->payment_id) {
                continue;
            }

            event(new OrderPlacedEvent($order));

            $order->is_finished = true;

            if (EcommerceHelper::isOrderAutoConfirmedEnabled()) {
                $order->is_confirmed = true;
            }

            $order->save();

            OrderHelper::decreaseProductQuantity($order);

            if (EcommerceHelper::isOrderAutoConfirmedEnabled()) {
                OrderHistory::query()->create([
                    'action' => 'confirm_order',
                    'description' => trans('plugins/ecommerce::order.order_was_verified_by'),
                    'order_id' => $order->id,
                    'user_id' => 0,
                ]);
            }
        }

        Cart::instance('cart')->destroy();
        session()->forget('applied_coupon_code');

        session(['order_id' => Arr::first($orderIds)]);

        if (is_plugin_active('marketplace')) {
            apply_filters(SEND_MAIL_AFTER_PROCESS_ORDER_MULTI_DATA, $orders);
        } else {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('admin_new_order')) {
                $this->setEmailVariables($orders->first());
                $mailer->sendUsingTemplate('admin_new_order', get_admin_email()->toArray());
            }

            // Temporarily only send emails with the first order
            $this->sendOrderConfirmationEmail($orders->first(), true);
        }

        session(['order_id' => $orders->first()->id]);

        foreach ($orders as $order) {
            OrderHistory::query()->create([
                'action' => 'create_order',
                'description' => trans('plugins/ecommerce::order.new_order_from', [
                    'order_id' => $order->code,
                    'customer' => BaseHelper::clean($order->user->name ?: $order->address->name),
                ]),
                'order_id' => $order->id,
            ]);

            if (is_plugin_active(
                'payment'
            ) && $order->payment && $order->payment->status == PaymentStatusEnum::COMPLETED) {
                $this->sendEmailForDigitalProducts($order);
            }
        }

        foreach ($orders as $order) {
            foreach ($order->products as $orderProduct) {
                $product = $orderProduct->product->original_product;

                $flashSale = $product->latestFlashSales()->first();
                if (! $flashSale) {
                    continue;
                }

                $flashSale->products()->detach([$product->id]);
                $flashSale->products()->attach([
                    $product->id => [
                        'price' => $flashSale->pivot->price,
                        'quantity' => (int)$flashSale->pivot->quantity,
                        'sold' => (int)$flashSale->pivot->sold + $orderProduct->qty,
                    ],
                ]);
            }
        }

        return $orders;
    }

    public function decreaseProductQuantity(Order $order): bool
    {
        foreach ($order->products as $orderProduct) {
            $product = Product::query()->find($orderProduct->product_id);

            if ($product) {
                if ($product->with_storehouse_management || $product->quantity >= $orderProduct->qty) {
                    $product->quantity = $product->quantity >= $orderProduct->qty ? $product->quantity - $orderProduct->qty : 0;
                    $product->save();

                    event(new ProductQuantityUpdatedEvent($product));
                }
            }
        }

        return true;
    }

    public function setEmailVariables(Order $order): EmailHandlerSupport
    {
        $paymentMethod = '&mdash;';

        if (is_plugin_active('payment')) {
            $paymentMethod = $order->payment->payment_channel->label();

            if ($order->payment->payment_channel == PaymentMethodEnum::BANK_TRANSFER && $order->payment->status == PaymentStatusEnum::PENDING) {
                $paymentMethod .= '<div>' . trans('plugins/ecommerce::order.payment_info') . ': <strong>' .
                    BaseHelper::clean(get_payment_setting('description', $order->payment->payment_channel)) .
                    '</strong</div>';
            }
        }

        return EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'store_address' => get_ecommerce_setting('store_address'),
                'store_phone' => get_ecommerce_setting('store_phone'),
                'order_id' => $order->code,
                'order_token' => $order->token,
                'order_note' => $order->description,
                'customer_name' => BaseHelper::clean($order->user->name ?: $order->address->name),
                'customer_email' => $order->user->email ?: $order->address->email,
                'customer_phone' => $order->user->phone ?: $order->address->phone,
                'customer_address' => $order->full_address,
                'product_list' => view('plugins/ecommerce::emails.partials.order-detail', compact('order'))
                    ->render(),
                'shipping_method' => $order->shipping_method_name,
                'payment_method' => $paymentMethod,
                'order_delivery_notes' => view(
                    'plugins/ecommerce::emails.partials.order-delivery-notes',
                    compact('order')
                )
                    ->render(),
            ]);
    }

    public function sendOrderConfirmationEmail(Order $order, bool $saveHistory = false): bool
    {
        try {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('customer_new_order')) {
                $this->setEmailVariables($order);

                EmailHandler::send(
                    $mailer->getTemplateContent('customer_new_order'),
                    $mailer->getTemplateSubject('customer_new_order'),
                    $order->user->email ?: $order->address->email
                );

                if ($saveHistory) {
                    OrderHistory::query()->create([
                        'action' => 'send_order_confirmation_email',
                        'description' => trans('plugins/ecommerce::order.confirmation_email_was_sent_to_customer'),
                        'order_id' => $order->id,
                    ]);
                }
            }

            return true;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return false;
    }

    public function sendEmailForDigitalProducts(Order $order): void
    {
        if (! EcommerceHelperFacade::isEnabledSupportDigitalProducts()) {
            return;
        }

        if (EcommerceHelperFacade::countDigitalProducts($order->products)) {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            $view = view('plugins/ecommerce::emails.partials.digital-product-list', compact('order'))->render();
            $mailer->setVariableValues([
                'customer_name' => BaseHelper::clean($order->user->name ?: $order->address->name),
                'payment_method' => is_plugin_active('payment') ? $order->payment->payment_channel->label() : '&mdash;',
                'digital_product_list' => $view,
            ]);
            $mailer->sendUsingTemplate('download_digital_products', $order->user->email ?: $order->address->email);

            if (EcommerceHelperFacade::countDigitalProducts($order->products) == $order->products->count()) {
                $this->setOrderCompleted($order->id, request(), Auth::id() ?? 0);
            }
        }
    }

    public function setOrderCompleted(int|string $orderId, Request $request, int|string $userId = 0): Order
    {
        // Update status and time order complete
        /** @var Order $order */
        $order = Order::query()->firstOrCreate(
            ['id' => $orderId],
            [
                'status' => OrderStatusEnum::COMPLETED,
                'completed_at' => Carbon::now(),
            ],
        );

        event(new OrderCompletedEvent($order));

        do_action(ACTION_AFTER_ORDER_STATUS_COMPLETED_ECOMMERCE, $order, $request);

        OrderHistory::query()->create([
            'action' => 'update_status',
            'description' => trans('plugins/ecommerce::shipping.order_confirmed_by'),
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);

        return $order;
    }

    /**
     * @deprecated
     */
    public function makeInvoicePDF(Order $order): PDFHelper|Dompdf
    {
        return InvoiceHelperFacade::makeInvoicePDF($order->invoice);
    }

    /**
     * @deprecated
     */
    public function generateInvoice(Order $order): string
    {
        return InvoiceHelperFacade::generateInvoice($order->invoice);
    }

    /**
     * @deprecated
     */
    public function downloadInvoice(Order $order): Response
    {
        return InvoiceHelperFacade::downloadInvoice($order->invoice);
    }

    /**
     * @deprecated
     */
    public function streamInvoice(Order $order): Response
    {
        return InvoiceHelperFacade::streamInvoice($order->invoice);
    }

    public function getShippingMethod(string $method, array|string|null $option = null): array|string|null
    {
        $name = null;

        if ($method == ShippingMethodEnum::DEFAULT) {
            if ($option) {
                $rule = ShippingRule::query()->find($option);
                if ($rule) {
                    $name = $rule->name;
                }
            }

            if (empty($name)) {
                $name = trans('plugins/ecommerce::order.default');
            }
        }

        if (! $name && ShippingMethodEnum::search($method)) {
            $name = ShippingMethodEnum::getLabel($method);
        }

        return $name ?: $method;
    }

    public function processHistoryVariables(OrderHistory|ShipmentHistory $history): string|null
    {
        $variables = [
            'order_id' => Html::link(
                route('orders.edit', $history->order->id),
                $history->order->code . ' <i class="fa fa-external-link-alt"></i>',
                ['target' => '_blank'],
                null,
                false
            )
                ->toHtml(),
            'user_name' => $history->user_id === 0 ? trans('plugins/ecommerce::order.system') :
                BaseHelper::clean(
                    $history->user ? $history->user->name : (
                        $history->order->user->name ?:
                            $history->order->address->name
                    )
                ),
        ];

        $content = $history->description;

        foreach ($variables as $key => $value) {
            $content = str_replace('% ' . $key . ' %', $value, $content);
            $content = str_replace('%' . $key . '%', $value, $content);
            $content = str_replace('% ' . $key . '%', $value, $content);
            $content = str_replace('%' . $key . ' %', $value, $content);
        }

        return $content;
    }

    public function setOrderSessionData(string|null $token, string|array $data): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = array_replace_recursive($this->getOrderSessionData($token), $data);

        $data = $this->cleanData($data);

        session([md5('checkout_address_information_' . $token) => $data]);

        return $data;
    }

    public function getOrderSessionToken(): string
    {
        if (session()->has('tracked_start_checkout')) {
            $token = session()->get('tracked_start_checkout');
        } else {
            $token = md5(Str::random(40));
            session(['tracked_start_checkout' => $token]);
        }

        return $token;
    }

    public function getOrderSessionData(string|null $token = null): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = [];
        $sessionKey = md5('checkout_address_information_' . $token);
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
        }

        return $this->cleanData($data);
    }

    public function cleanData(array $data): array
    {
        foreach ($data as $key => $item) {
            if (! is_string($item)) {
                continue;
            }

            $data[$key] = BaseHelper::clean($item);
        }

        return $data;
    }

    public function mergeOrderSessionData(string|null $token, string|array $data): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = array_merge($this->getOrderSessionData($token), $data);

        session([md5('checkout_address_information_' . $token) => $data]);

        return $this->cleanData($data);
    }

    public function clearSessions(string|null $token): void
    {
        Cart::instance('cart')->destroy();
        session()->forget('applied_coupon_code');
        session()->forget('order_id');
        session()->forget(md5('checkout_address_information_' . $token));
        session()->forget('tracked_start_checkout');
    }

    public function handleAddCart(Product $product, Request $request): array
    {
        $parentProduct = $product->original_product;

        $image = $product->image ?: $parentProduct->image;
        $options = [];
        if ($request->input('options')) {
            $options = $this->getProductOptionData($request->input('options'));
        }

        /**
         * Add cart to session
         */
        Cart::instance('cart')->add(
            $product->id,
            BaseHelper::clean($parentProduct->name ?: $product->name),
            $request->input('qty', 1),
            $product->original_price,
            [
                'image' => $image,
                'attributes' => $product->is_variation ? $product->variation_attributes : '',
                'taxRate' => $parentProduct->total_taxes_percentage,
                'options' => $options,
                'extras' => $request->input('extras', []),
                'sku' => $product->sku,
                'weight' => $product->weight,
            ]
        );

        return Cart::instance('cart')->content()->toArray();
    }

    public function getProductOptionData(array $data): array
    {
        $result = [];
        if (! empty($data)) {
            foreach ($data as $key => $option) {
                if (empty($option) || ! is_array($option)) {
                    continue;
                }

                $optionValue = OptionValue::query()
                    ->select(['option_value', 'affect_price', 'affect_type'])
                    ->where('option_id', $key);

                if ($option['option_type'] != 'field' && isset($option['values'])) {
                    if (is_array($option['values'])) {
                        $optionValue->whereIn('option_value', $option['values']);
                    } else {
                        $optionValue->whereIn('option_value', [0 => $option['values']]);
                    }
                }

                $result['optionCartValue'][$key] = $optionValue->get()->toArray();
                foreach ($result['optionCartValue'][$key] as &$item) {
                    $item['option_type'] = $option['option_type'];
                }

                if ($option['option_type'] == 'field' && isset($option['values']) && count(
                    $result['optionCartValue']
                ) > 0) {
                    $result['optionCartValue'][$key][0]['option_value'] = $option['values'];
                }
            }
        }

        $result['optionInfo'] = Option::query()->whereIn('id', array_keys($data))->get()->pluck('name', 'id')->toArray(
        );

        return $result;
    }

    public function processAddressOrder(int|string $currentUserId, array $sessionData, Request $request): array
    {
        $address = null;

        $sessionAddressId = Arr::get($sessionData, 'address_id');
        if ($currentUserId && ! $sessionAddressId) {
            $address = Address::query()
                ->where([
                    'customer_id' => $currentUserId,
                    'is_default' => true,
                ])
                ->first();

            if ($address) {
                $sessionData['address_id'] = $address->id;
            }
        } elseif ($request->input('address.address_id') && $request->input('address.address_id') !== 'new') {
            $address = Address::query()->find($request->input('address.address_id'));
            if (! empty($address)) {
                $sessionData['address_id'] = $address->getKey();
            }
        }

        if ($sessionAddressId && $sessionAddressId !== 'new') {
            $address = Address::query()->find($sessionAddressId);
        }

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
                'order_id' => Arr::get($sessionData, 'created_order_id', 0),
            ];
        } elseif ((array)$request->input('address', [])) {
            $addressData = array_merge(
                ['order_id' => Arr::get($sessionData, 'created_order_id', 0)],
                (array)$request->input('address', [])
            );
        } else {
            $addressData = [
                'name' => Arr::get($sessionData, 'name'),
                'phone' => Arr::get($sessionData, 'phone'),
                'email' => Arr::get($sessionData, 'email'),
                'country' => Arr::get($sessionData, 'country'),
                'state' => Arr::get($sessionData, 'state'),
                'city' => Arr::get($sessionData, 'city'),
                'address' => Arr::get($sessionData, 'address'),
                'zip_code' => Arr::get($sessionData, 'zip_code'),
                'order_id' => Arr::get($sessionData, 'created_order_id', 0),
            ];
        }

        return $this->checkAndCreateOrderAddress($addressData, $sessionData);
    }

    public function checkAndCreateOrderAddress(array $addressData, array $sessionData): array
    {
        $addressData = $this->cleanData($addressData);

        $this->storeOrderBillingAddress($addressData, $sessionData);

        if (! Arr::get($sessionData, 'is_save_order_shipping_address', true)) {
            if ($createdOrderId = Arr::get($sessionData, 'created_order_id')) {
                OrderAddress::query()
                    ->where([
                        'order_id' => $createdOrderId,
                        'type' => OrderAddressTypeEnum::SHIPPING,
                    ])
                    ->delete();
                Arr::forget($sessionData, 'created_order_address');
                Arr::forget($sessionData, 'created_order_address_id');
            }
        } elseif ($addressData && ! empty($addressData['name'])) {
            if (! isset($sessionData['created_order_address'])) {
                $createdOrderAddress = $this->createOrderAddress($addressData, $sessionData);
                if ($createdOrderAddress) {
                    $sessionData['created_order_address'] = true;
                    $sessionData['created_order_address_id'] = $createdOrderAddress->getKey();
                }
            } elseif (Arr::get($sessionData, 'created_order_address_id')) {
                $createdOrderAddress = $this->createOrderAddress($addressData, $sessionData);
                $sessionData['created_order_address'] = true;
                $sessionData['created_order_address_id'] = $createdOrderAddress->getKey();
            }
        }

        return $sessionData;
    }

    protected function storeOrderBillingAddress(array $data, array $sessionData = [])
    {
        if (! EcommerceHelperFacade::isBillingAddressEnabled()) {
            return false;
        }

        $orderId = Arr::get($data, 'order_id', Arr::get($data, 'created_order_id'));
        if ($orderId) {
            $billingAddressSameAsShippingAddress = Arr::get(
                $sessionData,
                'billing_address_same_as_shipping_address',
                '1'
            );
            if (! $billingAddressSameAsShippingAddress || ! Arr::get(
                $sessionData,
                'is_save_order_shipping_address',
                true
            )) {
                $addressData = Arr::only(
                    $sessionData,
                    ['name', 'phone', 'email', 'country', 'state', 'city', 'address', 'zip_code']
                );

                if ($billingAddressSameAsShippingAddress) {
                    $billingAddressData = $addressData;
                } else {
                    $billingAddressData = Arr::get($sessionData, 'billing_address', []);
                }

                $rules = EcommerceHelperFacade::getCustomerAddressValidationRules();
                $validator = Validator::make($billingAddressData, $rules);
                if ($validator->fails()) {
                    return false;
                }

                $billingAddressData['order_id'] = $orderId;
                $billingAddressData['type'] = OrderAddressTypeEnum::BILLING;

                OrderAddress::query()
                    ->where([
                        'order_id' => $orderId,
                        'type' => OrderAddressTypeEnum::BILLING,
                    ])
                    ->update($billingAddressData);
            } else {
                OrderAddress::query()
                    ->where([
                        'order_id' => $orderId,
                        'type' => OrderAddressTypeEnum::BILLING,
                    ])
                    ->delete();
            }
        }
    }

    protected function createOrderAddress(array $data, ?array $sessionData = []): OrderAddress|bool
    {
        $data['type'] = OrderAddressTypeEnum::SHIPPING;

        if ($orderId = Arr::get($sessionData, 'created_order_id')) {
            $orderAddress = OrderAddress::query()
                ->where([
                    'order_id' => $orderId,
                    'type' => OrderAddressTypeEnum::SHIPPING,
                ])
                ->firstOrNew();

            $orderAddress->fill($data);

            $orderAddress->save();

            return $orderAddress;
        }

        $rules = EcommerceHelperFacade::getCustomerAddressValidationRules();

        $products = Cart::instance('cart')->products();

        $countDigitalProducts = EcommerceHelperFacade::countDigitalProducts($products);
        if (! auth('customer')->check() && $countDigitalProducts) {
            $rules['email'] = 'required|max:60|min:6';
            if ($countDigitalProducts == $products->count()) {
                $keys = [
                    'country',
                    'state',
                    'city',
                    'address',
                    'phone',
                    'zip_code',
                ];
                $rules = (new CheckoutRequest())->removeRequired($rules, $keys);
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return false;
        }

        return OrderAddress::query()->create($data);
    }

    public function processOrderProductData(array|Collection $products, array $sessionData): array
    {
        $createdOrderProduct = Arr::get($sessionData, 'created_order_product');

        $cartItems = $products['products']->pluck('cartItem');

        $lastUpdatedAt = Cart::instance('cart')->getLastUpdatedAt();

        // Check latest updated at of cart
        if (! $createdOrderProduct || ! $createdOrderProduct->eq($lastUpdatedAt)) {
            $orderProducts = OrderProduct::query()
                ->where('order_id', $sessionData['created_order_id'])
                ->get();
            $productIds = [];
            foreach ($cartItems as $cartItem) {
                $productByCartItem = $products['products']->firstWhere('id', $cartItem->id);

                $data = [
                    'order_id' => $sessionData['created_order_id'],
                    'product_id' => $cartItem->id,
                    'product_name' => $cartItem->name,
                    'product_image' => $productByCartItem->original_product->image,
                    'qty' => $cartItem->qty,
                    'weight' => $productByCartItem->weight * $cartItem->qty,
                    'price' => $cartItem->price,
                    'tax_amount' => $cartItem->tax,
                    'options' => [],
                    'product_type' => $productByCartItem->product_type,
                ];

                if ($cartItem->options) {
                    $data['options'] = $cartItem->options;
                }

                if (isset($cartItem->options['options'])) {
                    $data['product_options'] = $cartItem->options['options'];
                }

                $orderProduct = $orderProducts->firstWhere('product_id', $cartItem->id);

                if ($orderProduct) {
                    $orderProduct->fill($data);
                    $orderProduct->save();
                } else {
                    OrderProduct::query()->create($data);
                }

                $productIds[] = $cartItem->id;
            }

            // Delete orderProducts not exists;
            foreach ($orderProducts as $orderProduct) {
                if (! in_array($orderProduct->product_id, $productIds)) {
                    $orderProduct->delete();
                }
            }

            $sessionData['created_order_product'] = $lastUpdatedAt;
        }

        return $sessionData;
    }

    /**
     * @param       $sessionData
     * @param       $request
     * @param       $cartItems
     * @param       $order
     * @param array $generalData
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function processOrderInCheckout(
        $sessionData,
        $request,
        $cartItems,
        $order,
        array $generalData
    ): array {
        $createdOrder = Arr::get($sessionData, 'created_order');
        $createdOrderId = Arr::get($sessionData, 'created_order_id');

        $lastUpdatedAt = Cart::instance('cart')->getLastUpdatedAt();

        $data = array_merge([
            'amount' => Cart::instance('cart')->rawTotalByItems($cartItems),
            'shipping_method' => $request->input('shipping_method', ShippingMethodEnum::DEFAULT),
            'shipping_option' => $request->input('shipping_option'),
            'tax_amount' => Cart::instance('cart')->rawTaxByItems($cartItems),
            'sub_total' => Cart::instance('cart')->rawSubTotalByItems($cartItems),
            'coupon_code' => session()->get('applied_coupon_code'),
        ], $generalData);

        if ($createdOrder && $createdOrderId) {
            if ($order && (is_string($createdOrder) || ! $createdOrder->eq($lastUpdatedAt))) {
                $order->fill($data);
            }
        }

        if (! $order) {
            $data = array_merge($data, [
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'is_finished' => false,
            ]);

            $order = Order::query()->create($data);
        }

        $sessionData['created_order'] = $lastUpdatedAt; // insert last updated at in here
        $sessionData['created_order_id'] = $order->id;

        return [$sessionData, $order];
    }

    public function createOrder(Request $request, int|string $currentUserId, string $token, array $cartItems)
    {
        $request->merge([
            'amount' => Cart::instance('cart')->rawTotalByItems($cartItems),
            'user_id' => $currentUserId,
            'shipping_method' => $request->input('shipping_method', ShippingMethodEnum::DEFAULT),
            'shipping_option' => $request->input('shipping_option'),
            'shipping_amount' => 0,
            'tax_amount' => Cart::instance('cart')->rawTaxByItems($cartItems),
            'sub_total' => Cart::instance('cart')->rawSubTotalByItems($cartItems),
            'coupon_code' => session()->get('applied_coupon_code'),
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'is_finished' => false,
            'token' => $token,
        ]);

        return Order::query()->create($request->input());
    }

    public function confirmPayment(Order $order): bool
    {
        if (! is_plugin_active('payment')) {
            return false;
        }

        $payment = $order->payment;

        if (! $payment) {
            return false;
        }

        $payment->status = PaymentStatusEnum::COMPLETED;
        $payment->amount = $payment->amount ?: 0;
        $payment->user_id = Auth::id();
        $payment->save();

        event(new OrderPaymentConfirmedEvent($order, Auth::user()));

        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('order_confirm_payment')) {
            OrderHelper::setEmailVariables($order);
            $mailer->sendUsingTemplate(
                'order_confirm_payment',
                $order->user->email ?: $order->address->email
            );
        }

        OrderHistory::query()->create([
            'action' => 'confirm_payment',
            'description' => trans('plugins/ecommerce::order.payment_was_confirmed_by', [
                'money' => format_price($order->amount),
            ]),
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        $this->sendEmailForDigitalProducts($order);

        return true;
    }

    public function cancelOrder(Order $order): Order
    {
        $order->status = OrderStatusEnum::CANCELED;
        $order->is_confirmed = true;
        $order->save();

        event(new OrderCancelledEvent($order));

        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;
            $product->quantity += $orderProduct->qty;
            $product->save();

            if ($product->is_variation) {
                $originalProduct = $product->original_product;

                if ($originalProduct->id != $product->id) {
                    $originalProduct->quantity += $orderProduct->qty;
                    $originalProduct->save();
                }
            }

            event(new ProductQuantityUpdatedEvent($product));
        }

        if ($order->coupon_code && $order->user_id) {
            Discount::getFacadeRoot()->afterOrderCancelled($order->coupon_code, $order->user_id);
        }

        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('customer_cancel_order')) {
            OrderHelper::setEmailVariables($order);
            $mailer->sendUsingTemplate(
                'customer_cancel_order',
                $order->user->email ?: $order->address->email
            );
        }

        return $order;
    }

    public function shippingStatusDelivered(Shipment $shipment, Request $request, int|string $userId = 0): Order
    {
        return $this->setOrderCompleted($shipment->order_id, $request, $userId);
    }

    public function getOrderBankInfo(Order|EloquentCollection $orders): string|null
    {
        if (! is_plugin_active('payment')) {
            return null;
        }

        try {
            if (! $orders instanceof EloquentCollection) {
                $collection = new EloquentCollection();
                $collection->add($orders);
                $orders = $collection;
            }

            $orders = $orders->filter(function ($item) {
                return $item->payment->payment_channel == PaymentMethodEnum::BANK_TRANSFER &&
                    $item->payment->status == PaymentStatusEnum::PENDING;
            });

            if ($orders->isEmpty()) {
                return null;
            }

            $bankInfo = get_payment_setting('description', $orders->first()->payment->payment_channel);

            $orderAmount = 0;
            $orderCode = '';

            if ($bankInfo) {
                foreach ($orders as $item) {
                    $orderAmount += $item->amount;
                    $orderCode .= $item->code . ', ';
                }

                $orderCode = rtrim(trim($orderCode), ',');
            }

            $bankInfo = view(
                'plugins/ecommerce::orders.partials.bank-transfer-info',
                compact('bankInfo', 'orderAmount', 'orderCode')
            )->render();

            return apply_filters('ecommerce_order_bank_info', $bankInfo, $orders);
        } catch (Throwable) {
            return null;
        }
    }
}
