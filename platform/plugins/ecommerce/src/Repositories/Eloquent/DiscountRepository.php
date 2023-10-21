<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Enums\DiscountTargetEnum;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Repositories\Interfaces\DiscountInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class DiscountRepository extends RepositoriesAbstract implements DiscountInterface
{
    public function getAvailablePromotions(array $with = [], bool $forProductSingle = false)
    {
        $data = $this->model
            ->where('type', DiscountTypeEnum::PROMOTION)
            ->where('start_date', '<=', Carbon::now())
            ->where(function (EloquentBuilder $query) {
                return $query
                    ->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now());
            })
            ->where(function (EloquentBuilder $query) use ($forProductSingle) {
                return $query
                    ->whereIn('target', [DiscountTargetEnum::ALL_ORDERS, DiscountTargetEnum::MINIMUM_ORDER_AMOUNT])
                    ->orWhere(function (EloquentBuilder $sub) use ($forProductSingle) {
                        $compare = '>';

                        if ($forProductSingle) {
                            $compare = '=';
                        }

                        return $sub
                            ->whereIn('target', [
                                DiscountTargetEnum::CUSTOMER,
                                DiscountTargetEnum::PRODUCT_COLLECTIONS,
                                DiscountTargetEnum::SPECIFIC_PRODUCT,
                                DiscountTargetEnum::PRODUCT_VARIANT,
                            ])
                            ->where('product_quantity', $compare, 1);
                    });
            });

        if ($with) {
            $data = $data->with($with);
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }

    public function getProductPriceBasedOnPromotion(array $productIds = [], array $productCollectionIds = [])
    {
        $data = $this->model
            ->where('type', DiscountTypeEnum::PROMOTION)
            ->where('start_date', '<=', Carbon::now())
            ->where(function (EloquentBuilder $query) use ($productIds, $productCollectionIds) {
                return $query
                    ->where(function (EloquentBuilder $sub) use ($productIds) {
                        return $sub
                            ->whereIn('target', [DiscountTargetEnum::SPECIFIC_PRODUCT, DiscountTargetEnum::PRODUCT_VARIANT])
                            ->whereHas('products', function ($whereHas) use ($productIds) {
                                return $whereHas->whereIn('ec_discount_products.product_id', $productIds);
                            });
                    })
                    ->orWhere(function (EloquentBuilder $sub) use ($productCollectionIds) {
                        return $sub
                            ->where('target', DiscountTargetEnum::PRODUCT_COLLECTIONS)
                            ->whereHas('productCollections', function (EloquentBuilder $whereHas) use ($productCollectionIds) {
                                return $whereHas->whereIn('ec_discount_product_collections.product_collection_id', $productCollectionIds);
                            });
                    })
                    ->orWhere(function (EloquentBuilder $sub) {
                        return $sub
                            ->where('target', DiscountTargetEnum::CUSTOMER)
                            ->whereHas('customers', function (EloquentBuilder $whereHas) {
                                $customerId = auth('customer')->check() ? auth('customer')->id() : -1;

                                return $whereHas->where('ec_discount_customers.customer_id', $customerId);
                            });
                    });
            })
            ->where(function (EloquentBuilder $query) {
                return $query
                    ->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now());
            })
            ->where('product_quantity', 1)
            ->select('ec_discounts.*');

        return $this->applyBeforeExecuteQuery($data, true)->get();
    }
}
