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
use Botble\Ecommerce\Forms\ProductTagForm;
use Botble\Ecommerce\Http\Requests\ProductTagRequest;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Ecommerce\Tables\ProductTagTable;
use Exception;
use Illuminate\Http\Request;

class ProductTagController extends BaseController
{
    public function index(ProductTagTable $table)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-tag.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-tag.create'));

        return $formBuilder->create(ProductTagForm::class)->renderForm();
    }

    public function store(ProductTagRequest $request, BaseHttpResponse $response)
    {
        $productTag = ProductTag::query()->create($request->input());

        event(new CreatedContentEvent(PRODUCT_TAG_MODULE_SCREEN_NAME, $request, $productTag));

        return $response
            ->setPreviousUrl(route('product-tag.index'))
            ->setNextUrl(route('product-tag.edit', $productTag->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $productTag = ProductTag::query()->findOrFail($id);

        event(new BeforeEditContentEvent($request, $productTag));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $productTag->name]));

        return $formBuilder->create(ProductTagForm::class, ['model' => $productTag])->renderForm();
    }

    public function update(int|string $id, ProductTagRequest $request, BaseHttpResponse $response)
    {
        $productTag = ProductTag::query()->findOrFail($id);

        $productTag->fill($request->input());
        $productTag->save();

        event(new UpdatedContentEvent(PRODUCT_TAG_MODULE_SCREEN_NAME, $request, $productTag));

        return $response
            ->setPreviousUrl(route('product-tag.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $productTag = ProductTag::query()->findOrFail($id);

            $productTag->delete();

            event(new DeletedContentEvent(PRODUCT_TAG_MODULE_SCREEN_NAME, $request, $productTag));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getAllTags()
    {
        return ProductTag::query()->pluck('name')->all();
    }
}
