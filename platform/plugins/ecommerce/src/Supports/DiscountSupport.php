<?php

namespace Botble\Ecommerce\Supports;

use Botble\Ecommerce\Enums\DiscountTargetEnum;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Repositories\Interfaces\DiscountInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DiscountSupport
{
    protected Collection|array $promotions = [];

    public int|string $customerId = 0;

    public function __construct()
    {
        if (! is_in_admin() && auth('customer')->check()) {
            $this->setCustomerId(auth('customer')->id());
        }
    }

    public function setCustomerId(int|string $customerId): self
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getCustomerId(): int|string
    {
        return $this->customerId;
    }

    public function promotionForProduct(array $productIds, array $productCollectionIds): ?Discount
    {
        if (! $this->promotions) {
            $this->getAvailablePromotions();
        }

        foreach ($this->promotions as $promotion) {
            switch ($promotion->target) {
                case DiscountTargetEnum::SPECIFIC_PRODUCT:
                case DiscountTargetEnum::PRODUCT_VARIANT:
                    foreach ($promotion->products as $product) {
                        if (in_array($product->id, $productIds)) {
                            return $promotion;
                        }
                    }

                    break;

                case DiscountTargetEnum::PRODUCT_COLLECTIONS:
                    foreach ($promotion->productCollections as $productCollection) {
                        if (in_array($productCollection->id, $productCollectionIds)) {
                            return $promotion;
                        }
                    }

                    break;

                case DiscountTargetEnum::CUSTOMER:
                    if ($this->customerId) {
                        foreach ($promotion->customers as $customer) {
                            if ($customer->id == $this->customerId) {
                                return $promotion;
                            }
                        }
                    }

                    break;
            }
        }

        return null;
    }

    public function getAvailablePromotions(bool $forProductSingle = true): Collection
    {
        if (! $this->promotions instanceof Collection) {
            $this->promotions = collect();
        }

        if ($this->promotions->count() == 0) {
            $this->promotions = app(DiscountInterface::class)
                ->getAvailablePromotions(['products', 'customers', 'productCollections'], $forProductSingle);
        }

        return $this->promotions;
    }

    public function afterOrderPlaced(string $couponCode, int|string|null $customerId = 0): void
    {
        $now = Carbon::now();

        $discount = Discount::query()
            ->where('code', $couponCode)
            ->where('type', DiscountTypeEnum::COUPON)
            ->where('start_date', '<=', $now)
            ->where(function (Builder $query) use ($now) {
                $query
                    ->whereNull('end_date')
                    ->orWhere('end_date', '>', $now);
            })
            ->first();

        if ($discount) {
            $discount->total_used++;
            $discount->save();

            if (func_num_args() == 1) {
                $customerId = auth('customer')->check() ? auth('customer')->id() : 0;
            }

            if ($discount->target === DiscountTargetEnum::ONCE_PER_CUSTOMER && $customerId) {
                $discount->usedByCustomers()->syncWithoutDetaching([$customerId]);
            }
        }
    }

    public function afterOrderCancelled(string $couponCode, int|string|null $customerId = 0): void
    {
        $discount = Discount::query()
            ->where('code', $couponCode)
            ->where('type', DiscountTypeEnum::COUPON)
            ->first();

        if ($discount) {
            $discount->total_used--;
            $discount->save();

            if (func_num_args() == 1) {
                $customerId = auth('customer')->check() ? auth('customer')->id() : 0;
            }

            if ($discount->target === DiscountTargetEnum::ONCE_PER_CUSTOMER && $customerId) {
                $discount->usedByCustomers()->detach($customerId);
            }
        }
    }
}
