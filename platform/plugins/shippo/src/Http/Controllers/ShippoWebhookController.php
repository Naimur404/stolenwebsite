<?php

namespace Botble\Shippo\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentHistoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentInterface;
use Botble\Shippo\Shippo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ShippoWebhookController extends BaseController
{
    public function __construct(
        protected ShipmentInterface $shipmentRepository,
        protected ShipmentHistoryInterface $shipmentHistoryRepository,
        protected Shippo $shippo
    ) {
    }

    public function index(Request $request, BaseHttpResponse $response)
    {
        $event = $request->input('event');
        $data = (array) $request->input('data', []);

        $transactionId = null;

        switch ($event) {
            case 'transaction_updated':
                $transactionId = Arr::get($data, 'object_id');

                break;
            case 'track_updated':
                $transactionId = Arr::get($data, 'tracking_status.object_id');

                break;
            default:
                $this->shippo->log([__LINE__, print_r($request->input(), true)]);

                break;
        }

        if (! $transactionId) {
            return $response;
        }

        $condition = [
            'tracking_id' => $transactionId,
        ];

        $shipment = $this->shipmentRepository->getFirstBy($condition);

        if (! $shipment) {
            $this->shippo->log([__LINE__, print_r($condition, true)]);

            return $response;
        }

        switch ($event) {
            case 'transaction_updated':
                $this->transactionUpdated($shipment, $data);

                break;
            case 'track_updated':
                $this->trackUpdated($shipment, $data);

                break;
        }

        return $response;
    }

    protected function transactionUpdated(Shipment $shipment, array $data)
    {
        $status = Arr::get($data, 'status');
        if ($status == 'REFUNDED') {
            $shipment->status = ShippingStatusEnum::CANCELED;
            $shipment->save();
        }

        $this->shipmentHistoryRepository->createOrUpdate([
            'action' => 'transaction_updated',
            'description' => trans('plugins/shippo::shippo.transaction.updated', [
                'tracking' => Arr::get($data, 'tracking_number'),
            ]),
            'order_id' => $shipment->order_id,
            'user_id' => 0,
            'shipment_id' => $shipment->id,
        ]);
    }

    protected function trackUpdated(Shipment $shipment, array $data)
    {
        $status = Arr::get($data, 'tracking_status.status');
        switch ($status) {
            case 'PRE_TRANSIT':

                break;
            case 'TRANSIT':
                $shipment->status = ShippingStatusEnum::DELIVERING;
                $shipment->save();

                break;
            case 'DELIVERED':
                $shipment->status = ShippingStatusEnum::DELIVERED;
                $shipment->date_shipped = Carbon::now();
                $shipment->save();

                OrderHelper::shippingStatusDelivered($shipment, request());

                break;
            case 'RETURNED':
                $shipment->status = ShippingStatusEnum::CANCELED;
                $shipment->save();

                break;
        }

        $this->shipmentHistoryRepository->createOrUpdate([
            'action' => 'track_updated',
            'description' => trans('plugins/shippo::shippo.tracking.statuses.' . Str::lower($status)),
            'order_id' => $shipment->order_id,
            'user_id' => 0,
            'shipment_id' => $shipment->id,
        ]);
    }
}
