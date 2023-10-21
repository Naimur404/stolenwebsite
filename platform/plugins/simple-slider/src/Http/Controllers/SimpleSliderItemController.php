<?php

namespace Botble\SimpleSlider\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\SimpleSlider\Forms\SimpleSliderItemForm;
use Botble\SimpleSlider\Http\Requests\SimpleSliderItemRequest;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Botble\SimpleSlider\Tables\SimpleSliderItemTable;
use Exception;
use Illuminate\Http\Request;

class SimpleSliderItemController extends BaseController
{
    public function index(SimpleSliderItemTable $dataTable)
    {
        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        return $formBuilder->create(SimpleSliderItemForm::class)
            ->setTitle(trans('plugins/simple-slider::simple-slider.create_new_slide'))
            ->setUseInlineJs(true)
            ->renderForm();
    }

    public function store(SimpleSliderItemRequest $request, BaseHttpResponse $response)
    {
        $simpleSlider = SimpleSliderItem::query()->create($request->input());

        event(new CreatedContentEvent(SIMPLE_SLIDER_ITEM_MODULE_SCREEN_NAME, $request, $simpleSlider));

        return $response->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder)
    {
        $simpleSliderItem = SimpleSliderItem::query()->findOrFail($id);

        return $formBuilder->create(SimpleSliderItemForm::class, ['model' => $simpleSliderItem])
            ->setTitle(trans('plugins/simple-slider::simple-slider.edit_slide', ['id' => $simpleSliderItem->getKey()]))
            ->setUseInlineJs(true)
            ->renderForm();
    }

    public function update(int|string $id, SimpleSliderItemRequest $request, BaseHttpResponse $response)
    {
        $simpleSliderItem = SimpleSliderItem::query()->findOrFail($id);
        $simpleSliderItem->fill($request->input());
        $simpleSliderItem->save();

        event(new UpdatedContentEvent(SIMPLE_SLIDER_ITEM_MODULE_SCREEN_NAME, $request, $simpleSliderItem));

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getDelete(int|string $id)
    {
        $simpleSliderItem = SimpleSliderItem::query()->findOrFail($id);

        return view('plugins/simple-slider::partials.delete', ['slider' => $simpleSliderItem])->render();
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $simpleSliderItem = SimpleSliderItem::query()->findOrFail($id);
            $simpleSliderItem->delete();

            event(new DeletedContentEvent(SIMPLE_SLIDER_ITEM_MODULE_SCREEN_NAME, $request, $simpleSliderItem));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
