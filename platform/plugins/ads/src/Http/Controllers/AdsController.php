<?php

namespace Botble\Ads\Http\Controllers;

use Botble\Ads\Forms\AdsForm;
use Botble\Ads\Http\Requests\AdsRequest;
use Botble\Ads\Models\Ads;
use Botble\Ads\Tables\AdsTable;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Exception;
use Illuminate\Http\Request;

class AdsController extends BaseController
{
    public function index(AdsTable $table)
    {
        PageTitle::setTitle(trans('plugins/ads::ads.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ads::ads.create'));

        return $formBuilder->create(AdsForm::class)->renderForm();
    }

    public function store(AdsRequest $request, BaseHttpResponse $response)
    {
        $ads = Ads::query()->create($request->input());

        event(new CreatedContentEvent(ADS_MODULE_SCREEN_NAME, $request, $ads));

        return $response
            ->setPreviousUrl(route('ads.index'))
            ->setNextUrl(route('ads.edit', $ads->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Ads $ads, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $ads));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $ads->name]));

        return $formBuilder->create(AdsForm::class, ['model' => $ads])->renderForm();
    }

    public function update(Ads $ads, AdsRequest $request, BaseHttpResponse $response)
    {
        $ads->fill($request->input());
        $ads->save();

        event(new UpdatedContentEvent(ADS_MODULE_SCREEN_NAME, $request, $ads));

        return $response
            ->setPreviousUrl(route('ads.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Request $request, Ads $ads, BaseHttpResponse $response)
    {
        try {
            $ads->delete();

            event(new DeletedContentEvent(ADS_MODULE_SCREEN_NAME, $request, $ads));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
