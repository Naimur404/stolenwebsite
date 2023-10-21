<?php

namespace Botble\Ecommerce\Widgets;

use Botble\Base\Widgets\Chart;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Widgets\Traits\HasCategory;

class CustomerChart extends Chart
{
    use HasCategory;

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::reports.customers_chart');
    }

    public function getOptions(): array
    {
        $data = Customer::query()
            ->groupBy('period')
            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->pluck('total', 'period')
            ->all();

        return [
            'series' => [
                [
                    'name' => trans('plugins/ecommerce::reports.number_of_customers'),
                    'data' => array_values($data),
                ],
            ],
            'xaxis' => [
                'categories' => $this->translateCategories($data),
            ],
        ];
    }
}
