<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Forms\BrandForm;
use Botble\Ecommerce\Http\Requests\BrandRequest;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Tables\BrandTable;
use Exception;
use Illuminate\Http\Request;

class BrandController extends BaseController
{
    public function index(BrandTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::brands.menu'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::brands.create'));

        return $formBuilder->create(BrandForm::class)->renderForm();
    }

    public function store(BrandRequest $request, BaseHttpResponse $response)
    {
        $brand = Brand::query()->create($request->input());

        if ($request->has('categories')) {
            $brand->categories()->sync((array) $request->input('categories', []));
        }

        event(new CreatedContentEvent(BRAND_MODULE_SCREEN_NAME, $request, $brand));

        return $response
            ->setPreviousUrl(route('brands.index'))
            ->setNextUrl(route('brands.edit', $brand->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Brand $brand, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $brand->name]));

        return $formBuilder->create(BrandForm::class, ['model' => $brand])->renderForm();
    }

    public function update(Brand $brand, BrandRequest $request, BaseHttpResponse $response)
    {
        $brand->fill($request->input());
        $brand->save();

        if ($request->has('categories')) {
            $brand->categories()->sync((array) $request->input('categories', []));
        }

        event(new UpdatedContentEvent(BRAND_MODULE_SCREEN_NAME, $request, $brand));

        return $response
            ->setPreviousUrl(route('brands.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Brand $brand, Request $request, BaseHttpResponse $response)
    {
        try {
            $brand->delete();

            event(new DeletedContentEvent(BRAND_MODULE_SCREEN_NAME, $request, $brand));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
