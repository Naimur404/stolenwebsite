<?php

namespace Botble\Ecommerce\Widgets;

use Botble\Base\Widgets\Chart;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Widgets\Traits\HasCategory;

class OrderChart extends Chart
{
    use HasCategory;

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::reports.orders_chart');
    }

    public function getOptions(): array
    {
        $data = Order::query()
            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->groupBy('period')
            ->pluck('total', 'period')
            ->all();

        return [
            'series' => [
                [
                    'name' => trans('plugins/ecommerce::reports.number_of_orders'),
                    'data' => array_values($data),
                ],
            ],
            'xaxis' => [
                'categories' => $this->translateCategories($data),
            ],
        ];
    }
}
