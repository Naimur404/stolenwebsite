<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Forms\GlobalOptionForm;
use Botble\Ecommerce\Http\Requests\GlobalOptionRequest;
use Botble\Ecommerce\Models\GlobalOption;
use Botble\Ecommerce\Tables\GlobalOptionTable;
use Exception;
use Illuminate\Http\Request;

class ProductOptionController extends BaseController
{
    public function index(GlobalOptionTable $table)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-option.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-option.create'));

        return $formBuilder->create(GlobalOptionForm::class)->renderForm();
    }

    public function store(GlobalOptionRequest $request, BaseHttpResponse $response)
    {
        $option = GlobalOption::query()->create($request->input());

        event(new CreatedContentEvent(GLOBAL_OPTION_MODULE_SCREEN_NAME, $request, $option));

        return $response
            ->setPreviousUrl(route('global-option.index'))
            ->setNextUrl(route('global-option.edit', $option->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $option = GlobalOption::query()->with(['values'])->findOrFail($id);

        event(new BeforeEditContentEvent($request, $option));

        PageTitle::setTitle(trans('plugins/ecommerce::product-option.edit', ['name' => $option->name]));

        return $formBuilder->create(GlobalOptionForm::class, ['model' => $option])->renderForm();
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $option = GlobalOption::query()->findOrFail($id);

            $option->delete();

            event(new DeletedContentEvent(GLOBAL_OPTION_MODULE_SCREEN_NAME, $request, $option));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function update(int|string $id, GlobalOptionRequest $request, BaseHttpResponse $response)
    {
        $option = GlobalOption::query()->findOrFail($id);

        $option->fill($request->input());
        $option->save();

        event(new UpdatedContentEvent(GLOBAL_OPTION_MODULE_SCREEN_NAME, $request, $option));

        return $response
            ->setPreviousUrl(route('global-option.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function ajaxInfo(Request $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $optionsValues = GlobalOption::query()->with(['values'])->findOrFail($request->input('id'));

        return $response->setData($optionsValues);
    }
}
