<?php

namespace Botble\Ecommerce\Tables\Reports;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class RecentOrdersTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(Order::class);

        $this->type = self::TABLE_TYPE_SIMPLE;
        $this->defaultSortColumn = 0;
        $this->view = 'core/table::simple-table';
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('code', function (Order $item) {
                if (! $this->hasPermission('orders.edit')) {
                    return $item->code;
                }

                return Html::link(route('orders.edit', $item->getKey()), $item->code);
            })
            ->editColumn('payment_status', function (Order $item) {
                if (! is_plugin_active('payment')) {
                    return '&mdash;';
                }

                return BaseHelper::clean($item->payment->status->label() ?: '&mdash;');
            })
            ->editColumn('payment_method', function (Order $item) {
                if (! is_plugin_active('payment')) {
                    return '&mdash;';
                }

                return BaseHelper::clean($item->payment->payment_channel->label() ?: '&mdash;');
            })
            ->editColumn('amount', function (Order $item) {
                return format_price($item->amount);
            })
            ->editColumn('shipping_amount', function (Order $item) {
                return format_price($item->shipping_amount);
            })
            ->editColumn('user_id', function (Order $item) {
                return BaseHelper::clean($item->user->name ?: $item->address->name);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport(request());

        $with = ['user'];

        if (is_plugin_active('payment')) {
            $with[] = 'payment';
        }

        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'status',
                'code',
                'user_id',
                'created_at',
                'amount',
                'tax_amount',
                'shipping_amount',
                'payment_id',
            ])
            ->with($with)
            ->where('is_finished', 1)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderByDesc('created_at')
            ->limit(10);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make('code'),
            Column::make('user_id')
                ->title(trans('plugins/ecommerce::order.customer_label'))
                ->alignLeft(),
            Column::make('amount')
                ->title(trans('plugins/ecommerce::order.amount')),
            Column::make('payment_method')
                ->name('payment_id')
                ->title(trans('plugins/ecommerce::order.payment_method'))
                ->alignLeft(),
            Column::make('payment_status')
                ->name('payment_id')
                ->title(trans('plugins/ecommerce::order.payment_status_label')),
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }
}
