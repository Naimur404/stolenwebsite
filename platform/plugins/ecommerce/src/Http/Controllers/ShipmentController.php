<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\ShippingStatusChanged;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Requests\UpdateShipmentCodStatusRequest;
use Botble\Ecommerce\Http\Requests\UpdateShipmentStatusRequest;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Ecommerce\Tables\ShipmentTable;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends BaseController
{
    public function index(ShipmentTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::shipping.shipments'));

        return $dataTable->renderTable();
    }

    public function edit(int|string $id)
    {
        Assets::addStylesDirectly('vendor/core/plugins/ecommerce/css/ecommerce.css')
            ->addScriptsDirectly('vendor/core/plugins/ecommerce/js/shipment.js');

        $shipment = Shipment::query()->findOrFail($id);

        PageTitle::setTitle(trans('plugins/ecommerce::shipping.edit_shipping', ['code' => get_shipment_code($id)]));

        return view('plugins/ecommerce::shipments.edit', compact('shipment'));
    }

    public function postUpdateStatus(int|string $id, UpdateShipmentStatusRequest $request, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);
        $previousShipment = $shipment->toArray();
        $shipment->status = $request->input('status');
        $shipment->save();

        ShipmentHistory::query()->create([
            'action' => 'update_status',
            'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                'status' => $shipment->status->label(),
            ]),
            'shipment_id' => $id,
            'order_id' => $shipment->order_id,
            'user_id' => Auth::id() ?? 0,
        ]);

        switch ($shipment->status) {
            case ShippingStatusEnum::DELIVERED:
                $shipment->date_shipped = Carbon::now();
                $shipment->save();

                OrderHelper::shippingStatusDelivered($shipment, $request, Auth::id() ?? 0);

                break;

            case ShippingStatusEnum::CANCELED:
                OrderHistory::query()->create([
                    'action' => 'cancel_shipment',
                    'description' => trans('plugins/ecommerce::shipping.shipping_canceled_by'),
                    'order_id' => $shipment->order_id,
                    'user_id' => Auth::id(),
                ]);

                break;
        }

        event(new ShippingStatusChanged($shipment, $previousShipment));

        return $response->setMessage(trans('plugins/ecommerce::shipping.update_shipping_status_success'));
    }

    public function postUpdateCodStatus(int|string $id, UpdateShipmentCodStatusRequest $request, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);
        $shipment->cod_status = $request->input('status');
        $shipment->save();

        if ($shipment->cod_status == ShippingCodStatusEnum::COMPLETED) {
            OrderHelper::confirmPayment($shipment->order);
        }

        ShipmentHistory::query()->create([
            'action' => 'update_cod_status',
            'description' => trans('plugins/ecommerce::shipping.updated_cod_status_by', [
                'status' => $shipment->cod_status->label(),
            ]),
            'shipment_id' => $id,
            'order_id' => $shipment->order_id,
            'user_id' => Auth::id() ?? 0,
        ]);

        return $response->setMessage(trans('plugins/ecommerce::shipping.update_cod_status_success'));
    }

    public function update(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $shipment->fill(
            $request->only([
                'tracking_id',
                'shipping_company_name',
                'tracking_link',
                'estimate_date_shipped',
                'note',
            ])
        );

        $shipment->save();

        return $response
            ->setPreviousUrl(route('ecommerce.shipments.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, BaseHttpResponse $response)
    {
        try {
            $review = Shipment::query()->findOrFail($id);
            $review->delete();

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
