<?php

namespace Botble\Ecommerce\Widgets;

use Botble\Base\Widgets\Table;

class TrendingProductsTable extends Table
{
    protected string $table = \Botble\Ecommerce\Tables\Reports\TrendingProductsTable::class;

    protected string $route = 'ecommerce.report.trending-products';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::reports.trending_products');
    }
}
