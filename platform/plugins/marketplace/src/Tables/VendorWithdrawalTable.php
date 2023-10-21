<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\Withdrawal;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class VendorWithdrawalTable extends TableAbstract
{
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, Withdrawal $model)
    {
        parent::__construct($table, $urlGenerator);

        $this->model = $model;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('fee', function (Withdrawal $item) {
                return format_price($item->fee);
            })
            ->editColumn('amount', function (Withdrawal $item) {
                return format_price($item->amount);
            })
            ->addColumn('operations', function (Withdrawal $item) {
                return view('plugins/marketplace::withdrawals.tables.actions', compact('item'))->render();
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'id',
                'fee',
                'amount',
                'status',
                'currency',
                'created_at',
            ])
            ->where('customer_id', auth('customer')->id());

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            'amount' => [
                'title' => trans('plugins/ecommerce::order.amount'),
            ],
            'fee' => [
                'title' => trans('plugins/ecommerce::shipping.fee'),
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
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

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.vendor.withdrawals.create'));
    }
}
