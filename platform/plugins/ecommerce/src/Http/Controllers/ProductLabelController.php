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
use Botble\Ecommerce\Forms\ProductLabelForm;
use Botble\Ecommerce\Http\Requests\ProductLabelRequest;
use Botble\Ecommerce\Models\ProductLabel;
use Botble\Ecommerce\Tables\ProductLabelTable;
use Exception;
use Illuminate\Http\Request;

class ProductLabelController extends BaseController
{
    public function index(ProductLabelTable $table)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-label.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::product-label.create'));

        return $formBuilder->create(ProductLabelForm::class)->renderForm();
    }

    public function store(ProductLabelRequest $request, BaseHttpResponse $response)
    {
        $productLabel = ProductLabel::query()->create($request->input());

        event(new CreatedContentEvent(PRODUCT_LABEL_MODULE_SCREEN_NAME, $request, $productLabel));

        return $response
            ->setPreviousUrl(route('product-label.index'))
            ->setNextUrl(route('product-label.edit', $productLabel->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $productLabel = ProductLabel::query()->findOrFail($id);

        event(new BeforeEditContentEvent($request, $productLabel));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $productLabel->name]));

        return $formBuilder->create(ProductLabelForm::class, ['model' => $productLabel])->renderForm();
    }

    public function update(int|string $id, ProductLabelRequest $request, BaseHttpResponse $response)
    {
        $productLabel = ProductLabel::query()->findOrFail($id);

        $productLabel->fill($request->input());
        $productLabel->save();

        event(new UpdatedContentEvent(PRODUCT_LABEL_MODULE_SCREEN_NAME, $request, $productLabel));

        return $response
            ->setPreviousUrl(route('product-label.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $productLabel = ProductLabel::query()->findOrFail($id);

            $productLabel->delete();

            event(new DeletedContentEvent(PRODUCT_LABEL_MODULE_SCREEN_NAME, $request, $productLabel));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
