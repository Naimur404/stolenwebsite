<?php

namespace Botble\Marketplace\Widgets;

use Botble\Base\Widgets\Table;
use Botble\Marketplace\Tables\StoreRevenueTable as BaseStoreRevenueTable;

class StoreRevenueTable extends Table
{
    protected string $table = BaseStoreRevenueTable::class;

    protected string $route = 'marketplace.reports.store-revenues';

    public function getLabel(): string
    {
        return trans('plugins/marketplace::marketplace.reports.store_revenues');
    }
}
