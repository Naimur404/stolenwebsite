<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class OrderReturnTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(OrderReturn::class)
            ->addActions([
                EditAction::make()->route('marketplace.vendor.order-returns.edit'),
                DeleteAction::make()->route('marketplace.vendor.order-returns.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('return_status', function ($item) {
                return BaseHelper::clean($item->return_status->toHtml());
            })
            ->editColumn('reason', function ($item) {
                return BaseHelper::clean($item->reason->toHtml());
            })
            ->editColumn('order_id', function ($item) {
                return BaseHelper::clean($item->order->code);
            })
            ->editColumn('user_id', function ($item) {
                if (! $item->customer->name) {
                    return '&mdash;';
                }

                return BaseHelper::clean($item->customer->name);
            })
            ->filter(function ($query) {
                $keyword = $this->request->input('search.value');
                if ($keyword) {
                    return $query
                        ->whereHas('items', function ($subQuery) use ($keyword) {
                            return $subQuery->where('product_name', 'LIKE', '%' . $keyword . '%');
                        })->orWhereHas('customer', function ($subQuery) use ($keyword) {
                            return $subQuery->where('name', 'LIKE', '%' . $keyword . '%');
                        });
                }

                return $query;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'id',
                'order_id',
                'user_id',
                'reason',
                'order_status',
                'return_status',
                'created_at',
            ])
            ->with(['customer', 'order', 'items'])
            ->withCount('items')
            ->where('store_id', auth('customer')->user()->store->id)
            ->orderBy('id', 'desc');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            'order_id' => [
                'title' => trans('plugins/ecommerce::order.order_id'),
                'class' => 'text-start',
            ],
            'user_id' => [
                'title' => trans('plugins/ecommerce::order.customer_label'),
                'class' => 'text-start',
            ],
            'items_count' => [
                'title' => trans('plugins/ecommerce::order.order_return_items_count'),
            ],
            'return_status' => [
                'title' => trans('core/base::tables.status'),
            ],
            CreatedAtColumn::make(),
        ];
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
