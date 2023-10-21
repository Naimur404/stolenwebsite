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
use Botble\Ecommerce\Forms\ProductCollectionForm;
use Botble\Ecommerce\Http\Requests\ProductCollectionRequest;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Tables\ProductCollectionTable;
use Exception;
use Illuminate\Http\Request;

class ProductCollectionController extends BaseController
{
    public function index(ProductCollectionTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-collections.name'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-collections.create'));

        return $formBuilder->create(ProductCollectionForm::class)->renderForm();
    }

    public function store(ProductCollectionRequest $request, BaseHttpResponse $response)
    {
        $productCollection = new ProductCollection();
        $productCollection->fill($request->input());

        $productCollection->slug = $request->input('slug');
        $productCollection->save();

        event(new CreatedContentEvent(PRODUCT_COLLECTION_MODULE_SCREEN_NAME, $request, $productCollection));

        return $response
            ->setPreviousUrl(route('product-collections.index'))
            ->setNextUrl(route('product-collections.edit', $productCollection->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $productCollection = ProductCollection::query()->findOrFail($id);

        event(new BeforeEditContentEvent($request, $productCollection));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $productCollection->name]));

        return $formBuilder
            ->create(ProductCollectionForm::class, ['model' => $productCollection])
            ->remove('slug')
            ->renderForm();
    }

    public function update(int|string $id, ProductCollectionRequest $request, BaseHttpResponse $response)
    {
        $productCollection = ProductCollection::query()->findOrFail($id);
        $productCollection->fill($request->input());
        $productCollection->save();

        if ($productIds = $request->input('collection_products', [])) {
            $productIds = explode(',', $productIds);
        }

        $productCollection->products()->sync($productIds);

        event(new UpdatedContentEvent(PRODUCT_COLLECTION_MODULE_SCREEN_NAME, $request, $productCollection));

        return $response
            ->setPreviousUrl(route('product-collections.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, BaseHttpResponse $response, Request $request)
    {
        $productCollection = ProductCollection::query()->findOrFail($id);

        try {
            $productCollection->delete();

            event(new DeletedContentEvent(PRODUCT_COLLECTION_MODULE_SCREEN_NAME, $request, $productCollection));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getListForSelect(BaseHttpResponse $response)
    {
        $productCollections = ProductCollection::query()
            ->select(['id', 'name'])
            ->get()
            ->toArray();

        return $response->setData($productCollections);
    }

    public function getProductCollection(int|string|null $id, BaseHttpResponse $response)
    {
        $productCollection = ProductCollection::query()->with('products')->find($id);

        return $response->setData(view(
            'plugins/ecommerce::product-collections.partials.products',
            compact('productCollection')
        )->render());
    }
}
