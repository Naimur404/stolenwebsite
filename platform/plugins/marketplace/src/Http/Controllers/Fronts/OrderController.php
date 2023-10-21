<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Requests\AddressRequest;
use Botble\Ecommerce\Http\Requests\UpdateOrderRequest;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Tables\OrderTable;
use Botble\Payment\Models\Payment;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    public function __construct()
    {
        Assets::setConfig(config('plugins.marketplace.assets', []));
    }

    public function index(OrderTable $table)
    {
        PageTitle::setTitle(__('Orders'));

        return $table->render(MarketplaceHelper::viewPath('dashboard.table.base'));
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

        $order = $this->findOrFail($id);

        $order->load(['products', 'user']);

        PageTitle::setTitle(trans('plugins/ecommerce::order.edit_order', ['code' => $order->code]));

        $weight = $order->products_weight;

        $defaultStore = get_primary_store_locator();

        return MarketplaceHelper::view('dashboard.orders.edit', compact('order', 'weight', 'defaultStore'));
    }

    public function update(int|string $id, UpdateOrderRequest $request, BaseHttpResponse $response)
    {
        $order = $this->findOrFail($id);
        $order->fill($request->input());
        $order->save();

        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, $request, $order));

        return $response
            ->setPreviousUrl(route('orders.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $order = $this->findOrFail($id);

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

    public function getGenerateInvoice(int|string $orderId)
    {
        $order = $this->findOrFail($orderId);

        return InvoiceHelper::downloadInvoice($order->invoice);
    }

    public function postConfirm(Request $request, BaseHttpResponse $response)
    {
        $order = $this->findOrFail($request->input('order_id'));

        $order->is_confirmed = 1;
        if ($order->status == OrderStatusEnum::PENDING) {
            $order->status = OrderStatusEnum::PROCESSING;
        }

        $order->save();

        OrderHistory::query()->create([
            'action' => 'confirm_order',
            'description' => trans('plugins/ecommerce::order.order_was_verified_by'),
            'order_id' => $order->id,
            'user_id' => 0,
        ]);

        $payment = Payment::query()->where('order_id', $order->id)->first();

        if ($payment) {
            $payment->user_id = 0;
            $payment->save();
        }

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
        $order = $this->findOrFail($id);

        $result = OrderHelper::sendOrderConfirmationEmail($order);

        if (! $result) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::order.error_when_sending_email'));
        }

        return $response->setMessage(trans('plugins/ecommerce::order.sent_confirmation_email_success'));
    }

    public function postUpdateShippingAddress(int|string $id, AddressRequest $request, BaseHttpResponse $response)
    {
        $address = OrderAddress::query()
            ->where('id', $id)
            ->whereHas('order', function ($query) {
                $query->where('store_id', auth('customer')->user()->store->id);
            })
            ->first();

        if ($address) {
            $order = $address->order;
        } else {
            if (! $orderId = $request->input('order_id')) {
                abort(404);
            }

            $order = $this->findOrFail($orderId);

            if ($order->address->id) {
                $address = $order->address;
            } else {
                $address = new OrderAddress();
                $address->order_id = $order->id;
            }
        }

        if ($order->status == OrderStatusEnum::CANCELED) {
            abort(401);
        }

        $address->fill($request->validated());
        $address->save();

        return $response
            ->setData([
                'line' => view('plugins/ecommerce::orders.shipping-address.line', compact('address'))->render(),
                'detail' => view('plugins/ecommerce::orders.shipping-address.detail', compact('address'))->render(),
            ])
            ->setMessage(trans('plugins/ecommerce::order.update_shipping_address_success'));
    }

    public function postCancelOrder(int|string $id, BaseHttpResponse $response)
    {
        $order = $this->findOrFail($id);

        if (! $order->canBeCanceledByAdmin()) {
            abort(403);
        }

        OrderHelper::cancelOrder($order);

        OrderHistory::query()->create([
            'action' => 'cancel_order',
            'description' => trans('plugins/ecommerce::order.order_was_canceled_by'),
            'order_id' => $order->id,
            'user_id' => 0,
        ]);

        return $response->setMessage(trans('plugins/ecommerce::order.customer.messages.cancel_success'));
    }

    protected function findOrFail(int|string $id): Order|Model|null
    {
        return Order::query()
            ->where([
                'id' => $id,
                'is_finished' => 1,
                'store_id' => auth('customer')->user()->store->id,
            ])
            ->firstOrFail();
    }
}
