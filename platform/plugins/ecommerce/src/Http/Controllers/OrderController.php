<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Requests\AddressRequest;
use Botble\Ecommerce\Http\Requests\ApplyCouponRequest;
use Botble\Ecommerce\Http\Requests\CreateOrderRequest;
use Botble\Ecommerce\Http\Requests\CreateShipmentRequest;
use Botble\Ecommerce\Http\Requests\RefundRequest;
use Botble\Ecommerce\Http\Requests\UpdateOrderRequest;
use Botble\Ecommerce\Http\Resources\CartItemResource;
use Botble\Ecommerce\Http\Resources\CustomerAddressResource;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\OrderTaxInformation;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Ecommerce\Models\StoreLocator;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Ecommerce\Tables\OrderIncompleteTable;
use Botble\Ecommerce\Tables\OrderTable;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends BaseController
{
    public function __construct(
        protected HandleShippingFeeService $shippingFeeService,
        protected HandleApplyCouponService $handleApplyCouponService,
        protected HandleApplyPromotionsService $applyPromotionsService
    ) {
    }

    public function index(OrderTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::order.menu'));

        return $dataTable->renderTable();
    }

    public function create()
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order-create.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        Assets::usingVueJS();

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        PageTitle::setTitle(trans('plugins/ecommerce::order.create'));

        return view('plugins/ecommerce::orders.create');
    }

    public function store(CreateOrderRequest $request, BaseHttpResponse $response)
    {
        $data = $this->getDataBeforeCreateOrder($request);
        if (Arr::get($data, 'error')) {
            return $response->setError()->setMessage(implode('; ', Arr::get($data, 'message', [])));
        }

        $customerId = Arr::get($data, 'customer_id') ?: 0;
        $userId = Auth::id();
        $paymentStatus = $request->input('payment_status');

        $request->merge([
            'amount' => Arr::get($data, 'total_amount'),
            'user_id' => $customerId,
            'shipping_method' => Arr::get($data, 'shipping_method') ?: ShippingMethodEnum::DEFAULT,
            'shipping_option' => Arr::get($data, 'shipping_option'),
            'shipping_amount' => Arr::get($data, 'shipping_amount'),
            'tax_amount' => Arr::get($data, 'tax_amount') ?: 0,
            'sub_total' => Arr::get($data, 'sub_amount') ?: 0,
            'coupon_code' => Arr::get($data, 'coupon_code'),
            'discount_amount' => Arr::get($data, 'discount_amount') ?: 0,
            'promotion_amount' => Arr::get($data, 'promotion_amount') ?: 0,
            'discount_description' => $request->input('discount_description'),
            'description' => $request->input('note'),
            'is_confirmed' => 1,
            'is_finished' => 1,
            'status' => OrderStatusEnum::PROCESSING,
        ]);

        $order = Order::query()->create($request->input());

        if ($order) {
            OrderHistory::query()->create([
                'action' => 'create_order_from_admin_page',
                'description' => trans('plugins/ecommerce::order.create_order_from_admin_page'),
                'order_id' => $order->getKey(),
            ]);

            OrderHistory::query()->create([
                'action' => 'create_order',
                'description' => trans(
                    'plugins/ecommerce::order.new_order',
                    ['order_id' => $order->code]
                ),
                'order_id' => $order->getKey(),
            ]);

            OrderHistory::query()->create([
                'action' => 'confirm_order',
                'description' => trans('plugins/ecommerce::order.order_was_verified_by'),
                'order_id' => $order->getKey(),
                'user_id' => $userId,
            ]);

            if (is_plugin_active('payment')) {
                $payment = Payment::query()->create([
                    'amount' => $order->amount,
                    'currency' => cms_currency()->getDefaultCurrency()->title,
                    'payment_channel' => $request->input('payment_method'),
                    'status' => $paymentStatus ?: PaymentStatusEnum::PENDING,
                    'payment_type' => 'confirm',
                    'order_id' => $order->id,
                    'charge_id' => Str::upper(Str::random(10)),
                    'user_id' => $userId,
                ]);

                $order->payment_id = $payment->id;
                $order->save();

                if ($paymentStatus == PaymentStatusEnum::COMPLETED) {
                    event(new OrderPaymentConfirmedEvent($order, Auth::user()));

                    OrderHistory::query()->create([
                        'action' => 'confirm_payment',
                        'description' => trans('plugins/ecommerce::order.payment_was_confirmed_by', [
                            'money' => format_price($order->amount),
                        ]),
                        'order_id' => $order->id,
                        'user_id' => $userId,
                    ]);
                }
            }

            if ($request->input('customer_address.name')) {
                OrderAddress::query()->create([
                    'name' => $request->input('customer_address.name'),
                    'phone' => $request->input('customer_address.phone'),
                    'email' => $request->input('customer_address.email'),
                    'state' => $request->input('customer_address.state'),
                    'city' => $request->input('customer_address.city'),
                    'zip_code' => $request->input('customer_address.zip_code'),
                    'country' => $request->input('customer_address.country'),
                    'address' => $request->input('customer_address.address'),
                    'order_id' => $order->id,
                ]);
            } elseif ($customerId) {
                $customer = Customer::query()->findOrFail($customerId);
                OrderAddress::query()->create([
                    'name' => $customer->name,
                    'phone' => $request->input('customer_address.phone') ?: $customer->phone,
                    'email' => $customer->email,
                    'order_id' => $order->id,
                ]);
            }

            foreach (Arr::get($data, 'products') as $productItem) {
                $productItem = $productItem->toArray($request);
                $quantity = Arr::get($productItem, 'quantity', 1);
                $orderProduct = [
                    'order_id' => $order->id,
                    'product_id' => Arr::get($productItem, 'id'),
                    'product_name' => Arr::get($productItem, 'name'),
                    'product_image' => Arr::get($productItem, 'image'),
                    'qty' => $quantity,
                    'weight' => Arr::get($productItem, 'weight'),
                    'price' => Arr::get($productItem, 'original_price'),
                    'tax_amount' => Arr::get($productItem, 'tax_price'),
                    'product_options' => Arr::get($productItem, 'cart_options.options'),
                    'options' => Arr::get($productItem, 'cart_options', []),
                    'product_type' => Arr::get($productItem, 'product_type'),
                ];

                OrderProduct::query()->create($orderProduct);

                $product = Product::query()->find(Arr::get($productItem, 'id'));
                if (! $product) {
                    continue;
                }

                $ids = [$product->id];
                if ($product->is_variation && $product->original_product) {
                    $ids[] = $product->original_product->id;
                }

                Product::query()
                    ->whereIn('id', $ids)
                    ->where('with_storehouse_management', 1)
                    ->where('quantity', '>=', $quantity)
                    ->decrement('quantity', $quantity);

                event(new ProductQuantityUpdatedEvent($product));
            }

            event(new OrderCreated($order));
        }

        if (Arr::get($data, 'is_available_shipping')) {
            $order->load(['shipment']);
            $shipment = $order->shipment;
            $shippingData = Arr::get($data, 'shipping');

            $shipment->update([
                'order_id' => $order->id,
                'user_id' => 0,
                'weight' => Arr::get($data, 'weight') ?: 0,
                'cod_amount' => (is_plugin_active(
                    'payment'
                ) && $order->payment->id && $order->payment->status != PaymentStatusEnum::COMPLETED) ? $order->amount : 0,
                'cod_status' => ShippingCodStatusEnum::PENDING,
                'type' => $order->shipping_method,
                'status' => ShippingStatusEnum::PENDING,
                'price' => $order->shipping_amount,
                'rate_id' => Arr::get($shippingData, 'id', ''),
                'shipment_id' => Arr::get($shippingData, 'shipment_id', ''),
                'shipping_company_name' => Arr::get($shippingData, 'company_name', ''),
            ]);
        } else {
            $order->shipment()->delete();
        }

        if ($couponCode = $request->input('coupon_code')) {
            Discount::getFacadeRoot()->afterOrderPlaced($couponCode, $customerId);
        }

        if (! Arr::get($data, 'is_available_shipping')
            && $paymentStatus === (is_plugin_active('payment') ? PaymentStatusEnum::COMPLETED : 'completed')) {
            OrderHelper::setOrderCompleted($order->id, $request, $userId);
        }

        return $response
            ->setData($order)
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        $order = Order::query()
            ->where(['id' => $id, 'is_finished' => 1])
            ->with(['products', 'user', 'shippingAddress', 'taxInformation'])
            ->firstOrFail();

        PageTitle::setTitle(trans('plugins/ecommerce::order.edit_order', ['code' => $order->code]));

        $weight = number_format(EcommerceHelper::validateOrderWeight($order->products_weight));

        $defaultStore = get_primary_store_locator();

        return view('plugins/ecommerce::orders.edit', compact('order', 'weight', 'defaultStore'));
    }

    public function update(int|string $id, UpdateOrderRequest $request, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($id);
        $order->fill($request->input());
        $order->save();

        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, $request, $order));

        return $response
            ->setPreviousUrl(route('orders.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($id);

        try {
            $order->delete();
            event(new DeletedContentEvent(ORDER_MODULE_SCREEN_NAME, $request, $order));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getGenerateInvoice(int|string $orderId, Request $request)
    {
        $order = Order::query()->findOrFail($orderId);

        if (! $order->isInvoiceAvailable()) {
            abort(404);
        }

        if ($request->input('type') == 'print') {
            return InvoiceHelper::streamInvoice($order->invoice);
        }

        return InvoiceHelper::downloadInvoice($order->invoice);
    }

    public function postConfirm(Request $request, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($request->input('order_id'));
        $order->is_confirmed = 1;
        if ($order->status == OrderStatusEnum::PENDING) {
            $order->status = OrderStatusEnum::PROCESSING;
        }

        $order->save();

        OrderHistory::query()->create([
            'action' => 'confirm_order',
            'description' => trans('plugins/ecommerce::order.order_was_verified_by'),
            'order_id' => $order->getKey(),
            'user_id' => Auth::id(),
        ]);

        $payment = Payment::query()->where('order_id', $order->getKey())->first();

        if ($payment) {
            $payment->user_id = Auth::id();
            $payment->save();
        }

        event(new OrderConfirmedEvent($order, Auth::user()));

        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('order_confirm')) {
            OrderHelper::setEmailVariables($order);
            $mailer->sendUsingTemplate(
                'order_confirm',
                $order->user->email ?: $order->address->email
            );
        }

        return $response->setMessage(trans('plugins/ecommerce::order.confirm_order_success'));
    }

    public function postResendOrderConfirmationEmail(int|string $id, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($id);
        $result = OrderHelper::sendOrderConfirmationEmail($order);

        if (! $result) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::order.error_when_sending_email'));
        }

        return $response->setMessage(trans('plugins/ecommerce::order.sent_confirmation_email_success'));
    }

    public function getShipmentForm(
        int|string $orderId,
        HandleShippingFeeService $shippingFeeService,
        Request $request,
        BaseHttpResponse $response
    ) {
        $order = Order::query()->findOrFail($orderId);

        if ($request->has('weight')) {
            $weight = $request->input('weight');
        } else {
            $weight = $order->products_weight;
        }

        $shippingData = [
            'address' => $order->address->address,
            'country' => $order->address->country,
            'state' => $order->address->state,
            'city' => $order->address->city,
            'weight' => $weight,
            'order_total' => $order->amount,
        ];

        $shipping = $shippingFeeService->execute($shippingData);

        $storeLocators = StoreLocator::query()->where('is_shipping_location', true)->get();

        $url = route('orders.create-shipment', $order->id);

        if ($request->has('view')) {
            return view(
                'plugins/ecommerce::orders.shipment-form',
                compact('order', 'weight', 'shipping', 'storeLocators', 'url')
            );
        }

        return $response->setData(
            view(
                'plugins/ecommerce::orders.shipment-form',
                compact('order', 'weight', 'shipping', 'storeLocators', 'url')
            )->render()
        );
    }

    public function postCreateShipment(int|string $id, CreateShipmentRequest $request, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($id);
        $result = $response;

        $shipment = [
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'weight' => $order->products_weight,
            'note' => $request->input('note'),
            'cod_amount' => $request->input('cod_amount') ?? (is_plugin_active(
                'payment'
            ) && $order->payment->status != PaymentStatusEnum::COMPLETED ? $order->amount : 0),
            'cod_status' => 'pending',
            'type' => $request->input('method'),
            'status' => ShippingStatusEnum::DELIVERING,
            'price' => $order->shipping_amount,
            'store_id' => $request->input('store_id'),
        ];

        $store = StoreLocator::query()->find($request->input('store_id'));

        if (! $store) {
            $shipment['store_id'] = StoreLocator::query()->where('is_primary', true)->value('id');
        }

        $result = $result->setMessage(trans('plugins/ecommerce::order.order_was_sent_to_shipping_team'));

        if (! $result->isError()) {
            $order->fill([
                'status' => OrderStatusEnum::PROCESSING,
                'shipping_method' => $request->input('method'),
                'shipping_option' => $request->input('option'),
            ]);
            $order->save();

            $shipment = Shipment::query()->create($shipment);

            OrderHistory::query()->create([
                'action' => 'create_shipment',
                'description' => $result->getMessage() . ' ' . trans('plugins/ecommerce::order.by_username'),
                'order_id' => $id,
                'user_id' => Auth::id(),
            ]);

            ShipmentHistory::query()->create([
                'action' => 'create_from_order',
                'description' => trans('plugins/ecommerce::order.shipping_was_created_from'),
                'shipment_id' => $shipment->id,
                'order_id' => $id,
                'user_id' => Auth::id(),
            ]);
        }

        return $result;
    }

    public function postCancelShipment(int|string $id, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $shipment->update(['status' => ShippingStatusEnum::CANCELED]);

        OrderHistory::query()->create([
            'action' => 'cancel_shipment',
            'description' => trans('plugins/ecommerce::order.shipping_was_canceled_by'),
            'order_id' => $shipment->order_id,
            'user_id' => Auth::id(),
        ]);

        return $response
            ->setData([
                'status' => ShippingStatusEnum::CANCELED,
                'status_text' => ShippingStatusEnum::CANCELED()->label(),
            ])
            ->setMessage(trans('plugins/ecommerce::order.shipping_was_canceled_success'));
    }

    public function postUpdateShippingAddress(int|string $id, AddressRequest $request, BaseHttpResponse $response)
    {
        $address = OrderAddress::query()->findOrFail($id);

        $address->fill($request->input());
        $address->save();

        if ($address->order->status == OrderStatusEnum::CANCELED) {
            abort(401);
        }

        return $response
            ->setData([
                'line' => view('plugins/ecommerce::orders.shipping-address.line', compact('address'))->render(),
                'detail' => view('plugins/ecommerce::orders.shipping-address.detail', compact('address'))->render(),
            ])
            ->setMessage(trans('plugins/ecommerce::order.update_shipping_address_success'));
    }

    public function postUpdateTaxInformation(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $validated = $request->validate([
            'company_tax_code' => 'required|string|min:3|max:20',
            'company_name' => 'required|string|min:3|max:120',
            'company_address' => 'required|string|min:3|max:255',
            'company_email' => 'required|email|min:6|max:60',
        ]);

        $taxInformation = OrderTaxInformation::query()
            ->with('order')
            ->findOrFail($id);

        $taxInformation->update($validated);

        if ($taxInformation->order->status === OrderStatusEnum::CANCELED) {
            abort(401);
        }

        return $response
            ->setData(view('plugins/ecommerce::orders.tax-information.detail', ['tax' => $taxInformation])->render())
            ->setMessage(trans('plugins/ecommerce::order.tax_info.update_success'));
    }

    public function postCancelOrder(int|string $id, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($id);

        if (! $order->canBeCanceledByAdmin()) {
            abort(403);
        }

        OrderHelper::cancelOrder($order);

        OrderHistory::query()->create([
            'action' => 'cancel_order',
            'description' => trans('plugins/ecommerce::order.order_was_canceled_by'),
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        return $response->setMessage(trans('plugins/ecommerce::order.cancel_success'));
    }

    public function postConfirmPayment(int|string $id, BaseHttpResponse $response)
    {
        $order = Order::query()->with(['payment'])->findOrFail($id);

        if ($order->status === OrderStatusEnum::PENDING) {
            $order->status = OrderStatusEnum::PROCESSING;
        }

        $order->save();

        OrderHelper::confirmPayment($order);

        return $response->setMessage(trans('plugins/ecommerce::order.confirm_payment_success'));
    }

    public function postRefund(int|string $id, RefundRequest $request, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($id);
        if (is_plugin_active('payment') && $request->input(
            'refund_amount'
        ) > ($order->payment->amount - $order->payment->refunded_amount)) {
            return $response
                ->setError()
                ->setMessage(
                    trans('plugins/ecommerce::order.refund_amount_invalid', [
                        'price' => format_price(
                            $order->payment->amount - $order->payment->refunded_amount,
                            get_application_currency()
                        ),
                    ])
                );
        }

        foreach ($request->input('products', []) as $productId => $quantity) {
            $orderProduct = OrderProduct::query()->where([
                'product_id' => $productId,
                'order_id' => $id,
            ])
                ->first();

            if ($quantity > ($orderProduct->qty - $orderProduct->restock_quantity)) {
                $response
                    ->setError()
                    ->setMessage(trans('plugins/ecommerce::order.number_of_products_invalid'));

                break;
            }
        }

        $response = apply_filters(ACTION_BEFORE_POST_ORDER_REFUND_ECOMMERCE, $response, $order, $request);

        if ($response->isError()) {
            return $response;
        }

        $payment = $order->payment;
        if (! $payment) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::order.cannot_found_payment_for_this_order'));
        }

        $refundAmount = $request->input('refund_amount');

        if ($paymentService = get_payment_is_support_refund_online($payment)) {
            $paymentResponse = (new $paymentService());
            if (method_exists($paymentService, 'setCurrency')) {
                $paymentResponse = $paymentResponse->setCurrency($payment->currency);
            }

            $optionRefunds = [
                'refund_note' => $request->input('refund_note'),
                'order_id' => $order->id,
            ];

            $paymentResponse = $paymentResponse->refundOrder($payment->charge_id, $refundAmount, $optionRefunds);

            if (Arr::get($paymentResponse, 'error', true)) {
                return $response
                    ->setError()
                    ->setMessage(Arr::get($paymentResponse, 'message', ''));
            }

            if (Arr::get($paymentResponse, 'data.refund_redirect_url')) {
                return $response
                    ->setNextUrl($paymentResponse['data']['refund_redirect_url'])
                    ->setData($paymentResponse['data'])
                    ->setMessage(Arr::get($paymentResponse, 'message', ''));
            }

            $refundData = (array)Arr::get($paymentResponse, 'data', []);

            $response->setData($refundData);

            $refundData['_data_request'] = $request->except(['_token']) + [
                    'currency' => $payment->currency,
                    'created_at' => Carbon::now(),
                ];
            $metadata = $payment->metadata;
            $refunds = Arr::get($metadata, 'refunds', []);
            $refunds[] = $refundData;
            Arr::set($metadata, 'refunds', $refunds);

            $payment->metadata = $metadata;
        }

        $payment->refunded_amount += $refundAmount;

        if ($payment->refunded_amount == $payment->amount) {
            $payment->status = PaymentStatusEnum::REFUNDED;
        }

        $payment->refund_note = $request->input('refund_note');
        $payment->save();

        foreach ($request->input('products', []) as $productId => $quantity) {
            $product = Product::query()->find($productId);

            if ($product && $product->with_storehouse_management) {
                $product->quantity += $quantity;
                $product->save();
            }

            $orderProduct = OrderProduct::query()->where([
                'product_id' => $productId,
                'order_id' => $id,
            ])
                ->first();

            if ($orderProduct) {
                $orderProduct->restock_quantity += $quantity;
                $orderProduct->save();
            }
        }

        if ($refundAmount > 0) {
            OrderHistory::query()->create([
                'action' => 'refund',
                'description' => trans('plugins/ecommerce::order.refund_success_with_price', [
                    'price' => format_price($refundAmount),
                ]),
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'extras' => json_encode([
                    'amount' => $refundAmount,
                    'method' => $payment->payment_channel ?? PaymentMethodEnum::COD,
                ]),
            ]);
        }

        $response->setMessage(trans('plugins/ecommerce::order.refund_success'));

        return apply_filters(ACTION_AFTER_POST_ORDER_REFUNDED_ECOMMERCE, $response, $order, $request);
    }

    public function getAvailableShippingMethods(
        Request $request,
        BaseHttpResponse $response,
        HandleShippingFeeService $shippingFeeService
    ) {
        $weight = 0;
        $orderAmount = 0;

        foreach ($request->input('products', []) as $productId) {
            $product = Product::query()->find($productId);
            if ($product) {
                $weight += $product->weight * $product->qty;
                $orderAmount += $product->front_sale_price;
            }
        }

        $weight = EcommerceHelper::validateOrderWeight($weight);

        $shippingData = [
            'address' => $request->input('address'),
            'country' => $request->input('country'),
            'state' => $request->input('state'),
            'city' => $request->input('city'),
            'weight' => $weight,
            'order_total' => $orderAmount,
        ];

        $shipping = $shippingFeeService->execute($shippingData);

        $result = [];
        foreach ($shipping as $key => $shippingItem) {
            foreach ($shippingItem as $subKey => $subShippingItem) {
                $result[$key . ';' . $subKey . ';' . $subShippingItem['price']] = [
                    'name' => $subShippingItem['name'],
                    'price' => format_price($subShippingItem['price'], null, true),
                ];
            }
        }

        return $response->setData($result);
    }

    public function postApplyCoupon(
        ApplyCouponRequest $request,
        HandleApplyCouponService $handleApplyCouponService,
        BaseHttpResponse $response
    ) {
        $result = $handleApplyCouponService->applyCouponWhenCreatingOrderFromAdmin($request);

        if ($result['error']) {
            return $response
                ->setError()
                ->withInput()
                ->setMessage($result['message']);
        }

        return $response
            ->setData(Arr::get($result, 'data', []))
            ->setMessage(
                trans(
                    'plugins/ecommerce::order.applied_coupon_success',
                    ['code' => $request->input('coupon_code')]
                )
            );
    }

    public function getReorder(Request $request, BaseHttpResponse $response)
    {
        if (! $request->input('order_id')) {
            return $response
                ->setError()
                ->setNextUrl(route('orders.index'))
                ->setMessage(trans('plugins/ecommerce::order.order_is_not_existed'));
        }

        PageTitle::setTitle(trans('plugins/ecommerce::order.reorder'));

        Assets::usingVueJS();

        $order = Order::query()->find($request->input('order_id'));

        if (! $order) {
            return $response
                ->setError()
                ->setNextUrl(route('orders.index'))
                ->setMessage(trans('plugins/ecommerce::order.order_is_not_existed'));
        }

        $productIds = $order->products->pluck('product_id')->all();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get();

        $cartItems = collect();
        foreach ($order->products as $orderProduct) {
            $product = $products->firstWhere('id', $orderProduct->product_id);
            if (! $product) {
                continue;
            }

            $options = [
                'options' => $orderProduct->product_options,
            ];

            $cartItem = CartItem::fromAttributes($product->id, $orderProduct->product_name, 0, $options);
            $cartItem->setQuantity($orderProduct ? $orderProduct->qty : 1);

            $cartItems[] = $cartItem;
        }

        $products = CartItemResource::collection($cartItems);

        $customer = null;
        $customerAddresses = [];
        $customerOrderNumbers = 0;
        if ($order->user_id) {
            $customer = Customer::query()->findOrFail($order->user_id);
            $customer->avatar = (string)$customer->avatar_url;

            if ($customer) {
                $customerOrderNumbers = $customer->orders()->count();
            }

            $customerAddresses = CustomerAddressResource::collection($customer->addresses);
        }

        $customerAddress = new CustomerAddressResource($order->address);

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order-create.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        return view(
            'plugins/ecommerce::orders.reorder',
            compact(
                'order',
                'products',
                'productIds',
                'customer',
                'customerAddresses',
                'customerAddress',
                'customerOrderNumbers'
            )
        );
    }

    public function getIncompleteList(OrderIncompleteTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::order.incomplete_order'));

        return $dataTable->renderTable();
    }

    public function getViewIncompleteOrder(int|string $id)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::order.incomplete_order'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order-incomplete.js',
            ]);

        $order = Order::query()
            ->where('id', $id)
            ->where('is_finished', 0)
            ->with(['products', 'user'])
            ->firstOrFail();

        $weight = number_format(EcommerceHelper::validateOrderWeight($order->products_weight));

        return view('plugins/ecommerce::orders.view-incomplete-order', compact('order', 'weight'));
    }

    public function markIncompleteOrderAsCompleted(int|string $id, BaseHttpResponse $response): BaseHttpResponse
    {
        $order = Order::query()
            ->where('is_finished', false)
            ->findOrFail($id);

        DB::transaction(function () use ($order) {
            $order->update(['is_finished' => true]);

            $order->histories()->create([
                'order_id' => $order->getKey(),
                'user_id' => Auth::user()->getKey(),
                'action' => 'mark_order_as_completed',
                'description' => trans('plugins/ecommerce::order.mark_as_completed.history', [
                    'admin' => Auth::user()->name,
                    'time' => Carbon::now(),
                ]),
            ]);
        });

        return $response
            ->setMessage(trans('plugins/ecommerce::order.mark_as_completed.success'))
            ->setData([
                'next_url' => route('orders.edit', $order->getKey()),
            ]);
    }

    public function postSendOrderRecoverEmail(int|string $id, BaseHttpResponse $response)
    {
        $order = Order::query()->findOrFail($id);

        $email = $order->user->email ?: $order->address->email;

        if (! $email) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::order.error_when_sending_email'));
        }

        try {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);

            $order->dont_show_order_info_in_product_list = true;
            OrderHelper::setEmailVariables($order);

            $mailer->sendUsingTemplate('order_recover', $email);

            return $response->setMessage(trans('plugins/ecommerce::order.sent_email_incomplete_order_success'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function checkDataBeforeCreateOrder(Request $request, BaseHttpResponse $response)
    {
        $data = $this->getDataBeforeCreateOrder($request);

        return $response
            ->setData($data)
            ->setError(Arr::get($data, 'error', false))
            ->setMessage(implode('; ', Arr::get($data, 'message', [])));
    }

    protected function getDataBeforeCreateOrder(Request $request): array
    {
        if ($customerId = $request->input('customer_id')) {
            Discount::getFacadeRoot()->setCustomerId($customerId);
        }

        $with = [
            'productCollections',
            'variationInfo',
            'variationInfo.configurableProduct',
            'variationProductAttributes',
        ];
        if (is_plugin_active('marketplacce')) {
            $with = array_merge($with, ['store', 'variationInfo.configurableProduct.store']);
        }

        $inputProducts = collect($request->input('products'));
        if ($productIds = $inputProducts->pluck('id')->all()) {
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->with($with)
                ->get();
        } else {
            $products = collect();
        }

        $weight = 0;
        $discountAmount = 0;
        $shippingAmount = 0;
        $isError = false;
        $message = [];

        $cartItems = collect();
        $stores = collect();
        $productItems = collect();

        foreach ($inputProducts as $inputProduct) {
            $productId = $inputProduct['id'];
            $product = $products->firstWhere('id', $productId);
            if (! $product) {
                continue;
            }
            $productName = $product->original_product->name ?: $product->name;

            if ($product->isOutOfStock()) {
                $isError = true;
                $message[] = __('Product :product is out of stock!', ['product' => $productName]);
            }

            $productOptions = [];
            if ($inputOptions = Arr::get($inputProduct, 'options') ?: []) {
                $productOptions = OrderHelper::getProductOptionData($inputOptions);
            }

            $cartItemsById = $cartItems->where('id', $productId);

            $inputQty = Arr::get($inputProduct, 'quantity') ?: 1;
            $qty = $inputQty;
            $qtySelected = 0;
            if ($cartItemsById->count()) {
                $qtySelected = $cartItemsById->sum('qty');
            }

            $originalQuantity = $product->quantity;
            $product->quantity = (int)$product->quantity - $qtySelected - $inputQty + 1;

            if ($product->quantity < 0) {
                $product->quantity = 0;
            }

            if ($product->isOutOfStock()) {
                $isError = true;
                $qty = $originalQuantity - $qtySelected;
                if ($qty == 0) {
                    $message[] = __('Product :product is out of stock!', ['product' => $productName]);

                    continue;
                } else {
                    $message[] = __(
                        'Product :product limited quantity allowed is :quantity',
                        ['product' => $productName, 'quantity' => $qty]
                    );
                }
            }

            $product->quantity = $originalQuantity;

            if ($product->original_product->options->where('required', true)->count()) {
                if (! $inputOptions) {
                    $isError = true;
                    $message[] = __('Please select product options!');
                } else {
                    $requiredOptions = $product->original_product->options->where('required', true);

                    foreach ($requiredOptions as $requiredOption) {
                        if (! Arr::get($inputOptions, $requiredOption->id . '.values')) {
                            $isError = true;
                            $message[] = trans(
                                'plugins/ecommerce::product-option.add_to_cart_value_required',
                                ['value' => $requiredOption->name]
                            );
                        }
                    }
                }
            }

            if (is_plugin_active('marketplace')) {
                $store = $product->original_product->store;
                if ($store->id) {
                    $productName .= ' (' . $store->name . ')';
                }
                $stores[] = $store;
            }

            $parentProduct = $product->original_product;

            $image = $product->image ?: $parentProduct->image;
            $taxRate = $parentProduct->total_taxes_percentage;
            $options = [
                'name' => $productName,
                'image' => $image,
                'attributes' => $product->is_variation ? $product->variation_attributes : '',
                'taxRate' => $taxRate,
                'options' => $productOptions,
                'extras' => [],
                'sku' => $product->sku,
                'weight' => $product->original_product->weight,
                'original_price' => $product->original_price,
                'product_link' => route('products.edit', $product->original_product->id),
                'product_type' => (string)$product->product_type,
            ];
            $price = $product->original_price;
            $price = Cart::getPriceByOptions($price, $productOptions);

            $cartItem = CartItem::fromAttributes(
                $product->id,
                BaseHelper::clean($parentProduct->name ?: $product->name),
                $price,
                $options
            );

            $cartItemExists = $cartItems->firstWhere('rowId', $cartItem->rowId);

            if (! $cartItemExists) {
                $cartItem->setQuantity($qty);
                $cartItem->setTaxRate($taxRate);

                $cartItems[] = $cartItem;
                if (! $product->isTypeDigital()) {
                    $weight += $product->original_product->weight * $qty;
                }
                $product->cartItem = $cartItem;
                $productItems[] = $product;
            }
        }

        if (is_plugin_active('marketplace')) {
            if (count(array_unique(array_filter($stores->pluck('id')->all()))) > 1) {
                $isError = true;
                $message[] = trans('plugins/marketplace::order.products_are_from_different_vendors');
            }
        }

        $subAmount = Cart::rawSubTotalByItems($cartItems);
        $taxAmount = Cart::rawTaxByItems($cartItems);
        $rawTotal = Cart::rawTotalByItems($cartItems);

        $cartData = [];

        Arr::set($cartData, 'rawTotal', $rawTotal);
        Arr::set($cartData, 'cartItems', $cartItems);
        Arr::set($cartData, 'countCart', Cart::countByItems($cartItems));
        Arr::set($cartData, 'productItems', $productItems);

        $isAvailableShipping = $productItems->count() && EcommerceHelper::isAvailableShipping($productItems);

        $weight = EcommerceHelper::validateOrderWeight($weight);

        $shippingMethods = [];

        if ($isAvailableShipping) {
            $origin = EcommerceHelper::getOriginAddress();
            $keys = ['name', 'company', 'address', 'country', 'state', 'city', 'zip_code', 'email', 'phone'];

            if (is_plugin_active('marketplace')) {
                if ($stores->count() && ($store = $stores->first()) && $store->id) {
                    $origin = Arr::only($store->toArray(), $keys);
                    if (! EcommerceHelper::isUsingInMultipleCountries()) {
                        $origin['country'] = EcommerceHelper::getFirstCountryId();
                    }
                }
            }

            $addressTo = Arr::only($request->input('customer_address', []), $keys);

            $items = [];
            foreach ($productItems as $product) {
                if (! $product->isTypeDigital()) {
                    $cartItem = $product->cartItem;
                    $items[$cartItem->rowId] = [
                        'weight' => $product->weight,
                        'length' => $product->length,
                        'wide' => $product->wide,
                        'height' => $product->height,
                        'name' => $product->name,
                        'description' => $product->description,
                        'qty' => $cartItem->qty,
                        'price' => $product->original_price,
                    ];
                }
            }

            $shippingData = [
                'address' => Arr::get($addressTo, 'address'),
                'country' => Arr::get($addressTo, 'country'),
                'state' => Arr::get($addressTo, 'state'),
                'city' => Arr::get($addressTo, 'city'),
                'weight' => $weight,
                'order_total' => $rawTotal,
                'address_to' => $addressTo,
                'origin' => $origin,
                'items' => $items,
                'extra' => [],
                'payment_method' => $request->input('payment_method'),
            ];

            $shipping = $this->shippingFeeService->execute($shippingData);

            foreach ($shipping as $key => $shippingItem) {
                foreach ($shippingItem as $subKey => $subShippingItem) {
                    $shippingMethods[$key . ';' . $subKey] = [
                        'name' => $subShippingItem['name'],
                        'price' => format_price($subShippingItem['price'], null, true),
                        'price_label' => format_price($subShippingItem['price']),
                        'method' => $key,
                        'option' => $subKey,
                        'title' => $subShippingItem['name'] . ' - ' . format_price($subShippingItem['price']),
                        'id' => Arr::get($subShippingItem, 'id'),
                        'shipment_id' => Arr::get($subShippingItem, 'shipment_id'),
                        'company_name' => Arr::get($subShippingItem, 'company_name'),
                    ];
                }
            }
        }

        $shippingMethodName = '';
        $shippingMethod = $request->input('shipping_method');
        $shippingOption = $request->input('shipping_option');
        $shippingType = $request->input('shipping_type');
        $shipping = [];

        if ($shippingType == 'free-shipping') {
            $shippingAmount = 0;
            $shippingMethodName = trans('plugins/ecommerce::order.free_shipping');
            $shippingMethod = 'default';
        } else {
            if ($shippingMethod && $shippingOption) {
                if ($shipping = Arr::get($shippingMethods, $shippingMethod . ';' . $shippingOption)) {
                    $shippingAmount = Arr::get($shipping, 'price') ?: 0;
                    $shippingMethodName = Arr::get($shipping, 'name');
                }
            }
            if (! $shippingMethodName) {
                if ($shipping = Arr::first($shippingMethods)) {
                    $shippingAmount = Arr::get($shipping, 'price') ?: 0;
                    $shippingMethodName = Arr::get($shipping, 'name');
                }
            }
            if (! $shippingMethodName) {
                $shippingMethod = 'default';
                $shippingOption = '';
            }
        }

        $promotionAmount = $this->applyPromotionsService->getPromotionDiscountAmount($cartData);

        Arr::set($cartData, 'promotion_discount_amount', $promotionAmount);

        if ($couponCode = trim($request->input('coupon_code'))) {
            $couponData = $this->handleApplyCouponService->applyCouponWhenCreatingOrderFromAdmin($request, $cartData);
            if (Arr::get($couponData, 'error')) {
                $isError = true;
                $message[] = Arr::get($couponData, 'message');
            } else {
                if (Arr::get($couponData, 'data.is_free_shipping')) {
                    $shippingAmount = 0;
                } else {
                    $discountAmount = Arr::get($couponData, 'data.discount_amount');
                    if (! $discountAmount) {
                        $isError = true;
                        $message[] = __('Coupon code is not valid or does not apply to the products');
                    }
                }
            }
        } else {
            $couponData = [];
            if ($discountCustomValue = max((float)$request->input('discount_custom_value'), 0)) {
                if ($request->input('discount_type') === 'percentage') {
                    $discountAmount = $rawTotal * min($discountCustomValue, 100) / 100;
                } else {
                    $discountAmount = $discountCustomValue;
                }
            }
        }

        $totalAmount = max($rawTotal - $promotionAmount - $discountAmount, 0) + $shippingAmount;

        $data = [
            'customer_id' => $customerId,
            'products' => CartItemResource::collection($cartItems),
            'shipping_methods' => $shippingMethods,
            'weight' => $weight,
            'promotion_amount' => $promotionAmount,
            'promotion_amount_label' => format_price($promotionAmount),
            'discount_amount' => $discountAmount,
            'discount_amount_label' => format_price($discountAmount),
            'sub_amount' => $subAmount,
            'sub_amount_label' => format_price($subAmount),
            'tax_amount' => $taxAmount,
            'tax_amount_label' => format_price($taxAmount),
            'shipping_amount' => $shippingAmount,
            'shipping_amount_label' => format_price($shippingAmount),
            'total_amount' => $totalAmount,
            'total_amount_label' => format_price($totalAmount),
            'coupon_data' => $couponData,
            'shipping' => $shipping,
            'shipping_method_name' => $shippingMethodName,
            'shipping_type' => $shippingType,
            'shipping_method' => $shippingMethod,
            'shipping_option' => $shippingOption,
            'coupon_code' => $couponCode,
            'is_available_shipping' => $isAvailableShipping,
            'update_context_data' => true,
            'error' => $isError,
            'message' => $message,
        ];

        if (is_plugin_active('marketplace')) {
            $data['store'] = $stores->first() ?: [];
        }

        return $data;
    }
}
