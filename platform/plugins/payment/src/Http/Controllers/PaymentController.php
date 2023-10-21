<?php

namespace Botble\Payment\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Http\Requests\PaymentMethodRequest;
use Botble\Payment\Http\Requests\UpdatePaymentRequest;
use Botble\Payment\Models\Payment;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Botble\Payment\Tables\PaymentTable;
use Botble\Setting\Supports\SettingStore;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class PaymentController extends Controller
{
    public function __construct(protected PaymentInterface $paymentRepository)
    {
    }

    public function index(PaymentTable $table)
    {
        PageTitle::setTitle(trans('plugins/payment::payment.name'));

        return $table->renderTable();
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $payment = Payment::query()->findOrFail($id);

            $payment->delete();

            event(new DeletedContentEvent(PAYMENT_MODULE_SCREEN_NAME, $request, $payment));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function show(int|string $id)
    {
        $payment = Payment::query()->findOrFail($id);

        PageTitle::setTitle(trans('plugins/payment::payment.view_transaction', ['charge_id' => $payment->charge_id]));

        $detail = apply_filters(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, null, $payment);

        $paymentStatuses = PaymentStatusEnum::labels();

        if ($payment->status != PaymentStatusEnum::PENDING) {
            Arr::forget($paymentStatuses, PaymentStatusEnum::PENDING);
        }

        Assets::addScriptsDirectly('vendor/core/plugins/payment/js/payment-detail.js');

        return view('plugins/payment::show', compact('payment', 'detail', 'paymentStatuses'));
    }

    public function methods()
    {
        PageTitle::setTitle(trans('plugins/payment::payment.payment_methods'));

        Assets::addStylesDirectly('vendor/core/plugins/payment/css/payment-methods.css')
            ->addScriptsDirectly('vendor/core/plugins/payment/js/payment-methods.js');

        return view('plugins/payment::settings.index');
    }

    public function updateSettings(Request $request, BaseHttpResponse $response, SettingStore $settingStore)
    {
        $data = $request->except(['_token']);
        foreach ($data as $settingKey => $settingValue) {
            $settingStore
                ->set($settingKey, $settingValue);
        }

        $settingStore->save();

        return $response->setMessage(trans('plugins/payment::payment.saved_payment_settings_success'));
    }

    public function updateMethods(PaymentMethodRequest $request, BaseHttpResponse $response, SettingStore $settingStore)
    {
        $type = $request->input('type');
        $data = $request->except(['_token', 'type']);
        foreach ($data as $settingKey => $settingValue) {
            $settingStore
                ->set($settingKey, $settingValue);
        }

        $settingStore
            ->set('payment_' . $type . '_status', 1)
            ->save();

        return $response->setMessage(trans('plugins/payment::payment.saved_payment_method_success'));
    }

    public function updateMethodStatus(Request $request, BaseHttpResponse $response, SettingStore $settingStore)
    {
        $settingStore
            ->set('payment_' . $request->input('type') . '_status', 0)
            ->save();

        return $response->setMessage(trans('plugins/payment::payment.turn_off_success'));
    }

    public function update(int|string $id, UpdatePaymentRequest $request, BaseHttpResponse $response)
    {
        $payment = Payment::query()->findOrFail($id);

        $this->paymentRepository->update(['id' => $payment->id], [
            'status' => $request->input('status'),
        ]);

        do_action(ACTION_AFTER_UPDATE_PAYMENT, $request, $payment);

        return $response
            ->setPreviousUrl(route('payment.show', $payment->id))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getRefundDetail(int|string $id, int|string $refundId, BaseHttpResponse $response)
    {
        $data = [];
        $payment = Payment::query()->findOrFail($id);

        $data = apply_filters(PAYMENT_FILTER_GET_REFUND_DETAIL, $data, $payment, $refundId);

        if (! Arr::get($data, 'error') && Arr::get($data, 'data', [])) {
            $metadata = $payment->metadata;
            $refunds = Arr::get($metadata, 'refunds', []);
            if ($refunds) {
                foreach ($refunds as $key => $refund) {
                    if (Arr::get($refund, '_refund_id') == $refundId) {
                        $refunds[$key] = array_merge($refunds[$key], (array) Arr::get($data, 'data'));
                    }
                }

                Arr::set($metadata, 'refunds', $refunds);
                $payment->metadata = $metadata;
                $payment->save();
            }
        }

        $view = Arr::get($data, 'view');

        if ($view) {
            $response->setData($view);
        }

        return $response
            ->setError((bool) Arr::get($data, 'error'))
            ->setMessage(Arr::get($data, 'message', ''));
    }
}
