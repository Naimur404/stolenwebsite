<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\OrderReturnStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderReturnHelper;
use Botble\Ecommerce\Http\Requests\UpdateOrderReturnRequest;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Ecommerce\Tables\OrderReturnTable;
use Exception;
use Illuminate\Http\Request;

class OrderReturnController extends BaseController
{
    public function index(OrderReturnTable $orderReturnTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::order.order_return'));

        return $orderReturnTable->renderTable();
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

        $returnRequest = OrderReturn::query()->with(['items', 'customer', 'order'])->findOrFail($id);

        PageTitle::setTitle(trans('plugins/ecommerce::order.edit_order_return', ['code' => $returnRequest->code]));

        $defaultStore = get_primary_store_locator();

        return view('plugins/ecommerce::order-returns.edit', compact('returnRequest', 'defaultStore'));
    }

    public function update(int|string $id, UpdateOrderReturnRequest $request, BaseHttpResponse $response)
    {
        $returnRequest = OrderReturn::query()->findOrFail($id);

        $data['return_status'] = $request->input('return_status');

        if ($returnRequest->return_status == $data['return_status'] ||
            $returnRequest->return_status == OrderReturnStatusEnum::CANCELED ||
            $returnRequest->return_status == OrderReturnStatusEnum::COMPLETED) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::order.notices.update_return_order_status_error'));
        }

        [$status, $returnRequest] = OrderReturnHelper::updateReturnOrder($returnRequest, $data);

        if (! $status) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::order.notices.update_return_order_status_error'));
        }

        return $response
            ->setNextUrl(route('order_returns.edit', $returnRequest->id))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $order = OrderReturn::query()->findOrFail($id);

        try {
            $order->delete();
            event(new DeletedContentEvent(ORDER_RETURN_MODULE_SCREEN_NAME, $request, $order));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
