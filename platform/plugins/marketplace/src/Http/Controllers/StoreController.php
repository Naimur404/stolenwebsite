<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Forms\StoreForm;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Tables\StoreTable;
use Exception;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    public function index(StoreTable $table)
    {
        PageTitle::setTitle(trans('plugins/marketplace::store.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/marketplace::store.create'));

        return $formBuilder->create(StoreForm::class)->renderForm();
    }

    public function store(StoreRequest $request, BaseHttpResponse $response)
    {
        $store = Store::query()->create($request->input());

        event(new CreatedContentEvent(STORE_MODULE_SCREEN_NAME, $request, $store));

        return $response
            ->setPreviousUrl(route('marketplace.store.index'))
            ->setNextUrl(route('marketplace.store.edit', $store->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $store = Store::query()->findOrFail($id);

        event(new BeforeEditContentEvent($request, $store));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $store->name]));

        return $formBuilder->create(StoreForm::class, ['model' => $store])->renderForm();
    }

    public function update(int|string $id, StoreRequest $request, BaseHttpResponse $response)
    {
        $store = Store::query()->findOrFail($id);

        $store->fill($request->input());
        $store->save();

        $customer = $store->customer;
        if ($customer && $customer->id) {
            $vendorInfo = $customer->vendorInfo;
            $vendorInfo->payout_payment_method = $request->input('payout_payment_method');
            $vendorInfo->bank_info = $request->input('bank_info', []);
            $vendorInfo->tax_info = $request->input('tax_info', []);
            $vendorInfo->save();
        }

        event(new UpdatedContentEvent(STORE_MODULE_SCREEN_NAME, $request, $store));

        return $response
            ->setPreviousUrl(route('marketplace.store.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $store = Store::query()->findOrFail($id);

            $store->delete();

            event(new DeletedContentEvent(STORE_MODULE_SCREEN_NAME, $request, $store));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
