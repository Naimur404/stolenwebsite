<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class OrderIncompleteTable extends OrderTable
{
    public function setup(): void
    {
        $this
            ->model(Order::class)
            ->addActions([
                Action::make('view')
                    ->icon('fa fa-eye')
                    ->label(trans('core/base::tables.edit'))
                    ->route('orders.view-incomplete-order')->permission('orders.edit'),
                DeleteAction::make()->route('orders.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('amount', function (Order $item) {
                return format_price($item->amount);
            })
            ->editColumn('user_id', function (Order $item) {
                return BaseHelper::clean($item->user->name ?: $item->address->name);
            })
            ->filter(function ($query) {
                if ($keyword = $this->request->input('search.value')) {
                    return $query
                        ->whereHas('address', function ($subQuery) use ($keyword) {
                            return $subQuery
                                ->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('phone', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('user', function ($subQuery) use ($keyword) {
                            return $subQuery
                                ->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('phone', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhere('code', 'LIKE', '%' . $keyword . '%');
                }

                return $query;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'user_id',
                'created_at',
                'amount',
            ])
            ->with(['user'])
            ->where('is_finished', 0);

        return $this->applyScopes($query);
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->isEmpty()) {
            return view('plugins/ecommerce::orders.incomplete-intro');
        }

        return parent::renderTable($data, $mergeData);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('user_id')
                ->title(trans('plugins/ecommerce::order.customer_label'))
                ->alignLeft(),
            Column::make('amount')
                ->title(trans('plugins/ecommerce::order.amount')),
            CreatedAtColumn::make(),
        ];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('orders.destroy'),
        ];
    }

    public function getFilters(): array
    {
        $filters = parent::getFilters();
        Arr::forget($filters, ['payment_method', 'payment_status', 'shipping_method']);

        return $filters;
    }
}
