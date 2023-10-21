<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Facades\MarketplaceHelper;

class ProductController extends BaseController
{
    public function approveProduct(int|string $id, BaseHttpResponse $response)
    {
        $product = Product::query()->findOrFail($id);

        $product->status = BaseStatusEnum::PUBLISHED;
        $product->approved_by = auth()->id();

        $product->save();

        if (MarketplaceHelper::getSetting('enable_product_approval', 1)) {
            $store = $product->store;

            EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'store_name' => $store->name,
                ])
                ->sendUsingTemplate('product-approved', $store->email);
        }

        return $response->setMessage(trans('plugins/marketplace::store.approve_product_success'));
    }
}
