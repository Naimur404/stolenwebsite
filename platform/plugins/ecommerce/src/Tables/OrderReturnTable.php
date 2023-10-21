<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\OrderReturnStatusEnum;
use Botble\Ecommerce\Facades\OrderReturnHelper;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
                EditAction::make()->route('order_returns.edit'),
                DeleteAction::make()->route('order_returns.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('return_status', function (OrderReturn $item) {
                return BaseHelper::clean($item->return_status->toHtml());
            })
            ->editColumn('reason', function (OrderReturn $item) {
                return BaseHelper::clean($item->reason->toHtml());
            })
            ->editColumn('order_id', function (OrderReturn $item) {
                return BaseHelper::clean($item->order->code);
            })
            ->editColumn('user_id', function (OrderReturn $item) {
                if (! $item->customer->name) {
                    return '&mdash;';
                }

                return BaseHelper::clean($item->customer->name);
            });

        $data = $data
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
        $query = $this
            ->getModel()
            ->query()
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
            ->orderByDesc('id');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('order_id')
                ->title(trans('plugins/ecommerce::order.order_id'))
                ->alignLeft(),
            Column::make('user_id')
                ->title(trans('plugins/ecommerce::order.customer_label'))
                ->alignLeft(),
            Column::make('items_count')
                ->title(trans('plugins/ecommerce::order.order_return_items_count'))
                ->orderable(false)
                ->searchable(false),
            Column::make('return_status')
                ->title(trans('core/base::tables.status')),
            CreatedAtColumn::make(),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'return_status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => OrderReturnStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', OrderReturnStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('order_returns.destroy'),
        ];
    }

    public function saveBulkChangeItem(Model|OrderReturn $item, string $inputKey, string|null $inputValue): Model|bool
    {
        if ($inputKey === 'status' && $inputValue == OrderReturnStatusEnum::CANCELED) {
            /**
             * @var OrderReturn $item
             */
            OrderReturnHelper::cancelReturnOrder($item);

            return $item;
        }

        return parent::saveBulkChangeItem($item, $inputKey, $inputValue);
    }
}
