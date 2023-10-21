<?php

namespace Botble\SimpleSlider\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\SimpleSlider\Forms\SimpleSliderForm;
use Botble\SimpleSlider\Http\Requests\SimpleSliderRequest;
use Botble\SimpleSlider\Models\SimpleSlider;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Botble\SimpleSlider\Tables\SimpleSliderTable;
use Exception;
use Illuminate\Http\Request;

class SimpleSliderController extends BaseController
{
    public function index(SimpleSliderTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/simple-slider::simple-slider.menu'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/simple-slider::simple-slider.create'));

        return $formBuilder
            ->create(SimpleSliderForm::class)
            ->removeMetaBox('slider-items')
            ->renderForm();
    }

    public function store(SimpleSliderRequest $request, BaseHttpResponse $response)
    {
        $simpleSlider = SimpleSlider::query()->create($request->input());

        event(new CreatedContentEvent(SIMPLE_SLIDER_MODULE_SCREEN_NAME, $request, $simpleSlider));

        return $response
            ->setPreviousUrl(route('simple-slider.index'))
            ->setNextUrl(route('simple-slider.edit', $simpleSlider->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(SimpleSlider $simpleSlider, FormBuilder $formBuilder)
    {
        Assets::addScripts(['blockui', 'sortable'])
            ->addScriptsDirectly(['vendor/core/plugins/simple-slider/js/simple-slider-admin.js']);

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $simpleSlider->name]));

        return $formBuilder
            ->create(SimpleSliderForm::class, ['model' => $simpleSlider])
            ->renderForm();
    }

    public function update(SimpleSlider $simpleSlider, SimpleSliderRequest $request, BaseHttpResponse $response)
    {
        $simpleSlider->fill($request->input());
        $simpleSlider->save();

        event(new UpdatedContentEvent(SIMPLE_SLIDER_MODULE_SCREEN_NAME, $request, $simpleSlider));

        return $response
            ->setPreviousUrl(route('simple-slider.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(SimpleSlider $simpleSlider, Request $request, BaseHttpResponse $response)
    {
        try {
            $simpleSlider->save();

            event(new DeletedContentEvent(SIMPLE_SLIDER_MODULE_SCREEN_NAME, $request, $simpleSlider));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function postSorting(Request $request, BaseHttpResponse $response)
    {
        foreach ($request->input('items', []) as $key => $id) {
            SimpleSliderItem::query()->where('id', $id)->update(['order' => ($key + 1)]);
        }

        return $response->setMessage(trans('plugins/simple-slider::simple-slider.update_slide_position_success'));
    }
}
