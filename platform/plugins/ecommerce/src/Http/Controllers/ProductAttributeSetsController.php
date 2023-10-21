<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Forms\ProductAttributeSetForm;
use Botble\Ecommerce\Http\Requests\ProductAttributeSetsRequest;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Services\ProductAttributes\StoreAttributeSetService;
use Botble\Ecommerce\Tables\ProductAttributeSetsTable;
use Exception;
use Illuminate\Http\Request;

class ProductAttributeSetsController extends BaseController
{
    public function index(ProductAttributeSetsTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-attributes.name'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-attributes.create'));

        Assets::addScripts(['spectrum', 'jquery-ui'])
            ->addStyles(['spectrum'])
            ->addStylesDirectly([
                asset('vendor/core/plugins/ecommerce/css/ecommerce-product-attributes.css'),
            ])
            ->addScriptsDirectly([
                asset('vendor/core/plugins/ecommerce/js/ecommerce-product-attributes.js'),
            ]);

        return $formBuilder->create(ProductAttributeSetForm::class)->renderForm();
    }

    public function store(
        ProductAttributeSetsRequest $request,
        StoreAttributeSetService $service,
        BaseHttpResponse $response
    ) {
        $productAttributeSet = $service->execute($request, new ProductAttributeSet());

        if ($request->has('categories')) {
            $productAttributeSet->categories()->sync((array) $request->input('categories', []));
        }

        return $response
            ->setPreviousUrl(route('product-attribute-sets.index'))
            ->setNextUrl(route('product-attribute-sets.edit', $productAttributeSet->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-attributes.edit'));

        $productAttributeSet = ProductAttributeSet::query()->findOrFail($id);

        Assets::addScripts(['spectrum', 'jquery-ui'])
            ->addStyles(['spectrum'])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/css/ecommerce-product-attributes.css',
            ])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/ecommerce-product-attributes.js',
            ]);

        return $formBuilder
            ->create(ProductAttributeSetForm::class, ['model' => $productAttributeSet])
            ->renderForm();
    }

    public function update(
        int|string $id,
        ProductAttributeSetsRequest $request,
        StoreAttributeSetService $service,
        BaseHttpResponse $response
    ) {
        $productAttributeSet = ProductAttributeSet::query()->findOrFail($id);

        $service->execute($request, $productAttributeSet);

        if ($request->has('categories')) {
            $productAttributeSet->categories()->sync((array) $request->input('categories', []));
        }

        return $response
            ->setPreviousUrl(route('product-attribute-sets.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $productAttributeSet = ProductAttributeSet::query()->findOrFail($id);
            $productAttributeSet->delete();
            event(new DeletedContentEvent(PRODUCT_ATTRIBUTE_SETS_MODULE_SCREEN_NAME, $request, $productAttributeSet));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
