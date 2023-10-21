<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Ecommerce\Models\StoreLocator;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\CategoryCommission;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Withdrawal;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class OrderEcommerceSeeder extends BaseSeeder
{
    public function run(): void
    {
        Order::query()->truncate();
        OrderProduct::query()->truncate();
        Shipment::query()->truncate();
        Payment::query()->truncate();
        OrderAddress::query()->truncate();
        OrderHistory::query()->truncate();
        ShipmentHistory::query()->truncate();

        $isMarketplace = is_plugin_active('marketplace');
        $storeLocators = StoreLocator::all();
        $currency = get_application_currency();

        $shippingFeeService = app(HandleShippingFeeService::class);

        $products = Product::query()->where('is_variation', 1)
            ->with([
                'variationInfo',
                'variationInfo.configurableProduct',
            ])
            ->get();

        if ($isMarketplace) {
            $customers = Customer::query()->where('is_vendor', 0)->with(['addresses'])->get();
            $products->load(['variationInfo.configurableProduct.store']);
        } else {
            $customers = Customer::with(['addresses'])->get();
        }

        $total = 20;
        for ($i = 0; $i < $total; $i++) {
            $customer = $customers->random();
            $address = $customer->addresses->first();

            if (! $address) {
                continue;
            }

            $orderProducts = $products->random(rand(2, 4));

            $groupedProducts = $this->group($orderProducts);

            foreach ($groupedProducts as $grouped) {
                $taxAmount = 0;
                $subTotal = 0;
                $weight = 0;
                foreach ($grouped['products'] as &$product) {
                    $qty = rand(1, 3);
                    $product->qty = $qty;
                    $subTotal += $qty * $product->price;
                    $weight += $qty * $product->weight;
                    $product->tax_amount = $this->getTax($product);
                    $taxAmount += $product->tax_amount;
                }

                $shippingData = [
                    'address' => $address->address,
                    'country' => $address->country,
                    'state' => $address->state,
                    'city' => $address->city,
                    'weight' => $weight ?: 0.1,
                    'order_total' => $subTotal,
                ];

                $isAvailableShipping = EcommerceHelper::isAvailableShipping($grouped['products']);

                $shippingMethod = $shippingFeeService->execute($shippingData, ShippingMethodEnum::DEFAULT);

                $shippingAmount = Arr::get(Arr::first($shippingMethod), 'price', 0);
                if (! $isAvailableShipping) {
                    $shippingAmount = 0;
                }

                $time = Carbon::now()->subMinutes(($total - $i) * 120 * rand(1, 10));

                $order = [
                    'amount' => $subTotal + $taxAmount + $shippingAmount,
                    'user_id' => $customer->id,
                    'shipping_method' => $isAvailableShipping ? ShippingMethodEnum::DEFAULT : '',
                    'shipping_option' => $isAvailableShipping ? 1 : null,
                    'shipping_amount' => $shippingAmount,
                    'tax_amount' => $taxAmount,
                    'sub_total' => $subTotal,
                    'coupon_code' => null,
                    'discount_amount' => 0,
                    'status' => OrderStatusEnum::PENDING,
                    'is_finished' => true,
                    'token' => Str::random(29),
                    'created_at' => $time,
                    'updated_at' => $time,
                    'is_confirmed' => true,
                ];

                $order = Order::query()->create($order);
                if ($isMarketplace) {
                    $order->store_id = $grouped['store']->id ?: 0;
                }

                foreach ($grouped['products'] as $groupedProduct) {
                    $data = [
                        'order_id' => $order->id,
                        'product_id' => $groupedProduct->id,
                        'product_name' => $groupedProduct->name,
                        'product_image' => $groupedProduct->image,
                        'qty' => $groupedProduct->qty,
                        'weight' => $groupedProduct->weight * $groupedProduct->qty,
                        'price' => $groupedProduct->price ?: 1,
                        'tax_amount' => $groupedProduct->tax_amount,
                        'options' => [],
                        'product_type' => $groupedProduct->product_type,
                        'times_downloaded' => $groupedProduct->isTypeDigital() ? rand(0, 10) : 0,
                    ];
                    OrderProduct::query()->create($data);
                }

                OrderAddress::query()->create([
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'email' => $address->email,
                    'country' => $address->country,
                    'state' => $address->state,
                    'city' => $address->city,
                    'address' => $address->address,
                    'zip_code' => $address->zip_code,
                    'order_id' => $order->id,
                ]);

                OrderHistory::query()->create([
                    'action' => 'create_order_from_seeder',
                    'description' => __('Order is created from the checkout page'),
                    'order_id' => $order->id,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);

                OrderHistory::query()->create([
                    'action' => 'confirm_order',
                    'description' => trans('plugins/ecommerce::order.order_was_verified_by'),
                    'order_id' => $order->id,
                    'user_id' => 0,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);

                $paymentStatus = PaymentStatusEnum::COMPLETED;
                $paymentMethod = Arr::random(PaymentMethodEnum::values());

                if (in_array($paymentMethod, [PaymentMethodEnum::COD, PaymentMethodEnum::BANK_TRANSFER])) {
                    $paymentStatus = PaymentStatusEnum::PENDING;
                }

                $payment = Payment::query()->create([
                    'amount' => $order->amount,
                    'currency' => $currency->title,
                    'payment_channel' => $paymentMethod,
                    'status' => $paymentStatus,
                    'payment_type' => 'confirm',
                    'order_id' => $order->id,
                    'charge_id' => Str::upper(Str::random(10)),
                    'user_id' => 0,
                    'customer_id' => $customer->id,
                    'customer_type' => Customer::class,
                ]);

                if ($paymentStatus == PaymentStatusEnum::COMPLETED) {
                    OrderHistory::query()->create([
                        'action' => 'confirm_payment',
                        'description' => trans('plugins/ecommerce::order.payment_was_confirmed_by', [
                            'money' => format_price($order->amount),
                        ]),
                        'order_id' => $order->id,
                        'user_id' => 0,
                    ]);
                }

                $order->payment_id = $payment->id;
                $order->save();

                InvoiceHelper::store($order);

                $shipmentStatus = Arr::random([ShippingStatusEnum::APPROVED, ShippingStatusEnum::DELIVERED]);
                $codAmount = 0;
                $codStatus = ShippingCodStatusEnum::COMPLETED;

                if ($paymentMethod == PaymentMethodEnum::COD) {
                    $codAmount = $order->amount;
                }

                if ($codAmount) {
                    $codStatus = ShippingCodStatusEnum::PENDING;
                }

                if ($isAvailableShipping) {
                    $shipment = Shipment::query()->create([
                        'status' => $shipmentStatus,
                        'order_id' => $order->id,
                        'weight' => $weight,
                        'note' => '',
                        'cod_amount' => $codAmount,
                        'cod_status' => $codStatus,
                        'price' => $order->shipping_amount,
                        'store_id' => $storeLocators->count() > 1 ? $storeLocators->random(1)->id : 0,
                        'tracking_id' => 'JJD00' . rand(1111111, 99999999),
                        'shipping_company_name' => Arr::random(['DHL', 'AliExpress', 'GHN', 'FastShipping']),
                        'tracking_link' => 'https://mydhl.express.dhl/us/en/tracking.html#/track-by-reference',
                        'estimate_date_shipped' => Carbon::now()->addDays(rand(1, 10)),
                        'date_shipped' => $shipmentStatus == ShippingStatusEnum::DELIVERED ? Carbon::now() : null,
                    ]);

                    OrderHistory::query()->create([
                        'action' => 'create_shipment',
                        'description' => __('Created shipment for order'),
                        'order_id' => $order->id,
                        'user_id' => 0,
                    ]);

                    ShipmentHistory::query()->create([
                        'action' => 'create_from_order',
                        'description' => trans('plugins/ecommerce::order.shipping_was_created_from'),
                        'shipment_id' => $shipment->id,
                        'order_id' => $order->id,
                        'user_id' => 0,
                        'created_at' => $time,
                        'updated_at' => $time,
                    ]);

                    ShipmentHistory::query()->create([
                        'action' => 'update_status',
                        'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                            'status' => ShippingStatusEnum::getLabel(ShippingStatusEnum::APPROVED),
                        ]),
                        'shipment_id' => $shipment->id,
                        'order_id' => $order->id,
                        'user_id' => 0,
                        'created_at' => Carbon::now()->subMinutes(($total - $i) * 120),
                    ]);

                    if ($shipmentStatus == ShippingStatusEnum::DELIVERED) {
                        $order = $this->setOrderCompleted($order, $shipmentStatus);

                        if ($payment->status !== PaymentStatusEnum::COMPLETED && $paymentMethod == PaymentMethodEnum::COD) {
                            $shipment->cod_status = ShippingCodStatusEnum::COMPLETED;
                            $shipment->save();

                            ShipmentHistory::query()->create([
                                'action' => 'update_cod_status',
                                'description' => trans('plugins/ecommerce::shipping.updated_cod_status_by', [
                                    'status' => ShippingCodStatusEnum::getLabel(ShippingCodStatusEnum::COMPLETED),
                                ]),
                                'shipment_id' => $shipment->id,
                                'order_id' => $order->id,
                                'user_id' => 0,
                            ]);
                        }

                        ShipmentHistory::query()->create([
                            'action' => 'update_status',
                            'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                                'status' => ShippingStatusEnum::getLabel($shipmentStatus),
                            ]),
                            'shipment_id' => $shipment->id,
                            'order_id' => $order->id,
                            'user_id' => 0,
                        ]);
                    }
                } elseif ($paymentStatus == PaymentStatusEnum::COMPLETED) {
                    $this->setOrderCompleted($order);
                }
            }
        }

        $orders = Order::with(['payment', 'shipment', 'products'])->get();
        if ($isMarketplace) {
            Revenue::query()->truncate();
            $orders->load(['store', 'store.customer']);
        }
        foreach ($orders as $order) {
            if ($order->payment->status == PaymentStatusEnum::COMPLETED && $order->shipment->status == ShippingStatusEnum::DELIVERED) {
                $order->status = OrderStatusEnum::COMPLETED;
                $order->completed_at = Carbon::now();
                $order->save();

                OrderHistory::query()->create([
                    'action' => 'update_status',
                    'description' => trans('plugins/ecommerce::shipping.order_confirmed_by'),
                    'order_id' => $order->id,
                    'user_id' => 0,
                ]);

                if ($isMarketplace && $order->store->id && $order->store->customer->id) {
                    $customer = $order->store->customer;
                    $vendorInfo = $customer->vendorInfo;

                    if ($vendorInfo->id) {
                        $fee = $this->calculatorCommissionFeeByProduct($order->products);
                        $amount = $order->amount - $fee;
                        $currentBalance = $customer->balance;

                        $amountByCurrency = $amount;
                        $time = Carbon::now()->subMinutes(($order->id + 1) * 120 * rand(1, 10));

                        $data = [
                            'sub_amount' => $order->amount,
                            'fee' => $fee,
                            'amount' => $amount,
                            'currency' => get_application_currency()->title,
                            'current_balance' => $currentBalance,
                            'customer_id' => $customer->getKey(),
                            'created_at' => $time,
                            'updated_at' => $time,
                            'type' => RevenueTypeEnum::ADD_AMOUNT,
                        ];

                        try {
                            DB::beginTransaction();

                            Revenue::query()->create(
                                array_merge([
                                    'order_id' => $order->id,
                                ], $data)
                            );

                            $vendorInfo->total_revenue += $amountByCurrency;

                            $vendorInfo->balance += $amountByCurrency;
                            $vendorInfo->total_fee += $fee;
                            $vendorInfo->save();
                            DB::commit();
                        } catch (Throwable $th) {
                            DB::rollBack();

                            throw $th;
                        }
                    }
                }
            }
        }

        if (! $isMarketplace) {
            return;
        }

        Withdrawal::query()->truncate();
        $vendors = Customer::query()->where('is_vendor', 1)->get();
        $fee = MarketplaceHelper::getSetting('fee_withdrawal', 0);
        foreach ($vendors as $vendor) {
            $vendorInfo = $vendor->vendorInfo;
            $rand = rand(1, 3);
            for ($i = 0; $i <= $rand; $i++) {
                $amount = rand(1, (int)($vendorInfo->balance / 3) - 10);

                if ($amount - $fee > 0) {
                    Withdrawal::query()->create([
                        'fee' => $fee,
                        'amount' => $amount,
                        'customer_id' => $vendor->getKey(),
                        'currency' => $currency->title,
                        'bank_info' => $vendorInfo->bank_info,
                        'description' => '',
                        'current_balance' => max($vendorInfo->balance, 0),
                        'status' => Arr::random([
                            WithdrawalStatusEnum::PENDING,
                            WithdrawalStatusEnum::COMPLETED,
                            WithdrawalStatusEnum::PROCESSING,
                        ]),
                    ]);

                    $vendorInfo->balance -= $amount + $fee;
                    $vendorInfo->save();
                }
            }
        }
    }

    private function calculatorCommissionFeeByProduct($orderProducts): float|int
    {
        $totalFee = 0;
        foreach ($orderProducts as $orderProduct) {
            $product = $orderProduct->product->toArray();
            if ($product['is_variation']) {
                $id = $product['variation_info']['configurable_product_id'];
            } else {
                $id = $orderProduct->product_id;
            }
            $product = Product::with(['categories'])->find($id);
            $listCategories = $product->categories->pluck('id')->all();
            $commissionSetting = CategoryCommission::query()->whereIn('product_category_id', $listCategories)->orderBy(
                'commission_percentage',
                'desc'
            )->first();
            $commissionFeePercentage = MarketplaceHelper::getSetting('fee_per_order', 0);
            if (! empty($commissionSetting)) {
                $commissionFeePercentage = $commissionSetting->commission_percentage;
            }
            $totalFee += $orderProduct->price * $commissionFeePercentage / 100;
        }

        return $totalFee;
    }

    public function group(Collection $products): array|Collection
    {
        $groupedProducts = collect();
        foreach ($products as $product) {
            $storeId = $product->original_product && $product->original_product->store_id ? $product->original_product->store_id : 0;
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

    public function getTax(Product $product): float|int
    {
        if (! EcommerceHelper::isTaxEnabled()) {
            return 0;
        }

        return $product->price * $product->tax->percentage / 100;
    }

    public function setOrderCompleted(Order $order, string $shipmentStatus = ''): Order
    {
        $order->status = OrderStatusEnum::COMPLETED;
        $order->completed_at = Carbon::now();
        $order->save();

        OrderHistory::query()->create([
            'action' => 'update_status',
            'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                'status' => ShippingStatusEnum::getLabel($shipmentStatus),
            ]),
            'order_id' => $order->getKey(),
            'user_id' => 0,
        ]);

        return $order;
    }
}
