<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\DiscountRequest;
use Botble\Marketplace\Tables\DiscountTable;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscountController extends BaseController
{
    public function index(DiscountTable $table)
    {
        PageTitle::setTitle(__('Coupons'));

        return $table->render(MarketplaceHelper::viewPath('dashboard.table.base'));
    }

    public function create()
    {
        PageTitle::setTitle(__('Create coupon'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/marketplace/js/discount.js',
            ])
            ->addScripts(['timepicker', 'input-mask', 'blockui'])
            ->addStyles(['timepicker']);

        Assets::usingVueJS();

        return MarketplaceHelper::view('dashboard.discounts.create');
    }

    protected function getStore()
    {
        return auth('customer')->user()->store;
    }

    public function store(DiscountRequest $request, BaseHttpResponse $response)
    {
        $request->merge([
            'can_use_with_promotion' => 0,
        ]);

        if ($request->input('is_unlimited')) {
            $request->merge(['quantity' => null]);
        }

        $request->merge([
            'start_date' => Carbon::parse($request->input('start_date') . ' ' . $request->input('start_time'))
                ->toDateTimeString(),
        ]);

        if ($request->has('end_date') && ! $request->has('unlimited_time')) {
            $request->merge([
                'end_date' => Carbon::parse($request->input('end_date') . ' ' . $request->input('end_time'))
                    ->toDateTimeString(),
            ]);
        } else {
            $request->merge([
                'end_date' => null,
            ]);
        }

        $discount = new Discount();

        $discount->fill($request->input());

        $discount->store_id = $this->getStore()->id;
        $discount->save();

        event(new CreatedContentEvent(DISCOUNT_MODULE_SCREEN_NAME, $request, $discount));

        return $response
            ->setNextUrl(route('marketplace.vendor.discounts.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $discount = $this->findOrFail($id);

        try {
            $discount->delete();

            event(new DeletedContentEvent(DISCOUNT_MODULE_SCREEN_NAME, $request, $discount));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function postGenerateCoupon(BaseHttpResponse $response)
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (Discount::query()->where('code', $code)->exists());

        return $response->setData($code);
    }

    protected function findOrFail(int|string $id): OrderReturn|Model|null
    {
        return Discount::query()
            ->where('id', $id)
            ->where('store_id', $this->getStore()->id)
            ->firstOrFail();
    }
}
