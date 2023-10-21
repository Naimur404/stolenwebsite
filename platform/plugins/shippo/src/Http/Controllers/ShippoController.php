<?php

namespace Botble\Shippo\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentHistoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentInterface;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Shippo\Shippo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Throwable;

class ShippoController extends BaseController
{
    protected string|int|null $userId = 0;

    public function __construct(
        protected ShipmentInterface $shipmentRepository,
        protected ShipmentHistoryInterface $shipmentHistoryRepository,
        protected Shippo $shippo
    ) {
        if (is_in_admin(true) && Auth::check()) {
            $this->userId = Auth::id();
        }
    }

    public function show(int $id, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);
        $this->check($shipment);

        $order = $shipment->order;

        $content = '';
        $errors = [];

        try {
            $shipmentShippo = $this->shippo->retrieveShipment($shipment->shipment_id);

            if ($shipmentShippo && Arr::get($shipmentShippo, 'status') == 'SUCCESS') {
                $rate = [];
                $payment = $order->payment;
                if ($payment->payment_channel->getValue() == PaymentMethodEnum::COD) {
                    $extra = Arr::get($shipmentShippo, 'extra', []);
                    $codAmount = Arr::get($extra, 'COD.amount');
                    if ($codAmount && $order->amount != $codAmount) {
                        Arr::set($shipmentShippo, 'extra.COD.amount', $order->amount);

                        $shipmentShippo = $this->refreshShipment($shipmentShippo, $order);
                        $rates = Arr::get($shipmentShippo, 'rates', []);
                        $rate = Arr::first($rates, function ($value) use ($order) {
                            return Arr::get($value, 'servicelevel.token') == $order->shipping_option;
                        });

                        if ($rate) {
                            $shipment->shipment_id = Arr::get($shipmentShippo, 'object_id');
                            $shipment->rate_id = Arr::get($rate, 'object_id');
                            $shipment->save();
                        }
                    }
                }

                if (! $rate) {
                    $rates = Arr::get($shipmentShippo, 'rates', []);
                    $rate = Arr::first($rates, function ($value) use ($shipment) {
                        return Arr::get($value, 'object_id') == $shipment->rate_id;
                    });
                }

                $content = view('plugins/shippo::info', compact('rate', 'shipmentShippo', 'shipment', 'order'))->render();
            } else {
                if (Arr::has($shipmentShippo, 'message')) {
                    $errors[] = Arr::get($shipmentShippo, 'message');
                } else {
                    $errors[] = trans('plugins/shippo::shippo.shipment_object_id_not_found', ['id' => $shipment->shipment_id]);
                }
            }
        } catch (Throwable $th) {
            $errors[] = $th->getMessage();
        }

        return $response->setError((bool) $errors)
            ->setData([
                'html' => $content,
                'errors' => $errors,
            ])
            ->setMessage($errors ? Arr::first($errors) : '');
    }

    public function createTransaction(int $id, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $this->check($shipment);

        if (! $this->shippo->canCreateTransaction($shipment)) {
            abort(404);
        }

        $message = trans('plugins/shippo::shippo.transaction.created_success');

        $errors = [];
        $responseData = [];

        try {
            $transaction = $this->shippo->createTransaction($shipment->rate_id);
            if (Arr::get($transaction, 'status') == 'SUCCESS') {
                $shipment->tracking_link = Arr::get($transaction, 'tracking_url_provider');
                $shipment->label_url = Arr::get($transaction, 'label_url');
                $shipment->tracking_id = Arr::get($transaction, 'object_id');
                $shipment->metadata = json_encode($transaction);
                $shipment->status = ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT;
                $shipment->save();

                $this->shipmentHistoryRepository->createOrUpdate([
                    'action' => 'create_transaction',
                    'description' => trans('plugins/shippo::shippo.transaction.created', [
                        'tracking' => Arr::get($transaction, 'tracking_number'),
                    ]),
                    'order_id' => $shipment->order_id,
                    'user_id' => $this->userId,
                    'shipment_id' => $shipment->id,
                ]);

                $this->shipmentHistoryRepository->createOrUpdate([
                    'action' => 'update_status',
                    'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                        'status' => ShippingStatusEnum::getLabel(ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT),
                    ]),
                    'order_id' => $shipment->order_id,
                    'user_id' => $this->userId,
                    'shipment_id' => $shipment->id,
                ]);
            } else {
                if ($errors = Arr::get($transaction, 'messages', [])) {
                    $message = collect($errors)->pluck('text')->implode('; ');
                }
            }
        } catch (Exception $ex) {
            $errors[] = $ex->getMessage();
            $message = $ex->getMessage();
        }

        $responseData['errors'] = (array) $errors;

        return $response->setError((bool) count($errors))
            ->setMessage($message)
            ->setData($responseData);
    }

    protected function refreshShipment(array $shipmentShippo, Order $order)
    {
        if (! Arr::has($shipmentShippo, 'extra.reference_2')) {
            Arr::set($shipmentShippo, 'extra.reference_2', $order->code);
        }

        $params = [
            'address_from' => Arr::get($shipmentShippo, 'address_from.object_id'),
            'address_to' => Arr::get($shipmentShippo, 'address_to.object_id'),
            'extra' => Arr::get($shipmentShippo, 'extra'),
            'parcels' => [Arr::get($shipmentShippo, 'parcels.0.object_id')],
        ];

        if (Arr::has($shipmentShippo, 'customs_declaration')) {
            $params['customs_declaration'] = Arr::get($shipmentShippo, 'customs_declaration');
        }

        if (Arr::has($shipmentShippo, 'metadata')) {
            $params['metadata'] = Arr::get($shipmentShippo, 'metadata');
        }

        return $this->shippo->createShipment($params);
    }

    public function getRates(int $id, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $this->check($shipment);

        $content = '';
        $errors = [];
        $order = $shipment->order;

        try {
            $shipmentShippo = $this->shippo->retrieveShipment($shipment->shipment_id);

            $shipmentShippo = $this->refreshShipment($shipmentShippo, $order);
            $rates = Arr::get($shipmentShippo, 'rates', []);

            $rates = $this->shippo->sortRates($rates);

            $rate = Arr::first($rates, function ($value) use ($order) {
                return Arr::get($value, 'servicelevel.token') == $order->shipping_option;
            });

            if ($rate) {
                $rates = Arr::where($rates, function ($value) use ($rate) {
                    return Arr::get($value, 'servicelevel.token') !== Arr::get($rate, 'servicelevel.token');
                });
            }

            $content = view('plugins/shippo::rates', compact('rates', 'shipmentShippo', 'shipment', 'order', 'rate'))->render();
        } catch (Throwable $th) {
            $errors[] = $th->getMessage();
        }

        return $response->setError((bool) $errors)
            ->setData([
                'html' => $content,
                'errors' => $errors,
            ])
            ->setMessage($errors ? Arr::first($errors) : '');
    }

    public function updateRate(int $id, Request $request, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $this->check($shipment);

        $order = $shipment->order;

        $content = '';
        $errors = [];

        try {
            $shipmentShippo = $this->shippo->retrieveShipment($shipment->shipment_id);

            $shipmentShippo = $this->refreshShipment($shipmentShippo, $order);

            $rates = Arr::get($shipmentShippo, 'rates', []);
            $rates = $this->shippo->sortRates($rates);

            $rate = Arr::first($rates, function ($value) use ($order) {
                return Arr::get($value, 'servicelevel.token') == $order->shipping_option;
            });

            if (! $rate) {
                $rate = Arr::first($rates, function ($value) use ($request) {
                    return Arr::get($value, 'servicelevel.token') == $request->input('shipping_option');
                });
            }

            if ($rate) {
                $order->shipping_option = Arr::get($rate, 'servicelevel.token');
                $order->save();
                $shipment->shipment_id = Arr::get($shipmentShippo, 'object_id');
                $shipment->rate_id = Arr::get($rate, 'object_id');
                $shipment->save();

                $content = view('plugins/shippo::info', compact('rate', 'shipmentShippo', 'shipment', 'order'))->render();
            } else {
                $errors[] = 'Rate not found';
            }
        } catch (Throwable $th) {
            $errors[] = $th->getMessage();
        }

        return $response->setError((bool) $errors)
            ->setData([
                'html' => $content,
                'errors' => $errors,
            ])
            ->setMessage($errors ? Arr::first($errors) : trans('plugins/shippo::shippo.updated_rate_success'));
    }

    protected function check(Shipment $shipment): bool
    {
        $order = $shipment->order;

        if (! is_in_admin(true) && is_plugin_active('marketplace')) {
            $vendor = auth('customer')->user();
            $store = $vendor->store;

            if ($store->id != $order->store_id) {
                abort(403);
            }
        }

        if (! $order
            || ! $order->id
            || $order->shipping_method->getValue() != SHIPPO_SHIPPING_METHOD_NAME
            || ! $shipment->shipment_id) {
            abort(404);
        }

        return true;
    }

    public function viewLog(string $logFile)
    {
        $logPath = storage_path('logs/' . $logFile);

        if (! File::exists($logPath)) {
            abort(404);
        }

        return nl2br(File::get(storage_path('logs/' . $logFile)));
    }
}
