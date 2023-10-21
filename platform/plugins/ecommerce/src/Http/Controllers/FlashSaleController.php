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
use Botble\Ecommerce\Forms\FlashSaleForm;
use Botble\Ecommerce\Http\Requests\FlashSaleRequest;
use Botble\Ecommerce\Models\FlashSale;
use Botble\Ecommerce\Tables\FlashSaleTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FlashSaleController extends BaseController
{
    public function index(FlashSaleTable $table)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::flash-sale.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::flash-sale.create'));

        return $formBuilder->create(FlashSaleForm::class)->renderForm();
    }

    public function store(FlashSaleRequest $request, BaseHttpResponse $response)
    {
        $flashSale = FlashSale::query()->create($request->input());

        event(new CreatedContentEvent(FLASH_SALE_MODULE_SCREEN_NAME, $request, $flashSale));

        $this->storeProducts($request, $flashSale);

        return $response
            ->setPreviousUrl(route('flash-sale.index'))
            ->setNextUrl(route('flash-sale.edit', $flashSale->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    protected function storeProducts(FlashSaleRequest $request, FlashSale $flashSale)
    {
        $products = array_filter(explode(',', $request->input('products')));

        $flashSale->products()->detach();

        foreach ($products as $index => $productId) {
            if (! (int)$productId) {
                continue;
            }

            $extra = Arr::get($request->input('products_extra', []), $index);

            if (! $extra || ! isset($extra['price']) || ! isset($extra['quantity'])) {
                continue;
            }

            $extra['price'] = (float)$extra['price'];
            $extra['quantity'] = (int)$extra['quantity'];

            if ($flashSale->products()->where('id', $productId)->count()) {
                $flashSale->products()->sync([(int)$productId => $extra]);
            } else {
                $flashSale->products()->attach($productId, $extra);
            }
        }

        return count($products);
    }

    public function edit(FlashSale $flashSale, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $flashSale));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $flashSale->name]));

        return $formBuilder->create(FlashSaleForm::class, ['model' => $flashSale])->renderForm();
    }

    public function update(FlashSale $flashSale, FlashSaleRequest $request, BaseHttpResponse $response)
    {
        $flashSale->fill($request->input());
        $flashSale->save();

        $this->storeProducts($request, $flashSale);

        event(new UpdatedContentEvent(FLASH_SALE_MODULE_SCREEN_NAME, $request, $flashSale));

        return $response
            ->setPreviousUrl(route('flash-sale.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(FlashSale $flashSale, Request $request, BaseHttpResponse $response)
    {
        try {
            $flashSale->delete();

            event(new DeletedContentEvent(FLASH_SALE_MODULE_SCREEN_NAME, $request, $flashSale));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
