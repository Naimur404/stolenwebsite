<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Repositories\Interfaces\OrderInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends RepositoriesAbstract implements OrderInterface
{
    public function getRevenueData(CarbonInterface $startDate, CarbonInterface $endDate, $select = []): Collection
    {
        return Order::getRevenueData($startDate, $endDate);
    }

    public function countRevenueByDateRange(CarbonInterface $startDate, CarbonInterface $endDate): float
    {
        return Order::countRevenueByDateRange($startDate, $endDate);
    }
}
