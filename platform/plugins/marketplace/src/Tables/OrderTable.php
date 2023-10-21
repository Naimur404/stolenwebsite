<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class OrderTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Order::class)
            ->addActions([
                EditAction::make()->route('marketplace.vendor.orders.edit'),
                DeleteAction::make()->route('marketplace.vendor.orders.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('payment_status', function ($item) {
                return $item->payment->status->label() ? BaseHelper::clean($item->payment->status->toHtml()) : '&mdash;';
            })
            ->editColumn('payment_method', function ($item) {
                return BaseHelper::clean($item->payment->payment_channel->label() ?: '&mdash;');
            })
            ->editColumn('amount', function ($item) {
                return format_price($item->amount);
            })
            ->editColumn('shipping_amount', function ($item) {
                return format_price($item->shipping_amount);
            })
            ->editColumn('user_id', function ($item) {
                return BaseHelper::clean($item->user->name ?: $item->address->name);
            });

        if (EcommerceHelper::isTaxEnabled()) {
            $data = $data->editColumn('tax_amount', function ($item) {
                return format_price($item->tax_amount);
            });
        }

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'id',
                'status',
                'user_id',
                'created_at',
                'amount',
                'tax_amount',
                'shipping_amount',
                'payment_id',
            ])
            ->with(['user', 'payment'])
            ->where('is_finished', 1)
            ->where('store_id', auth('customer')->user()->store->id);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [
            IdColumn::make(),
            'user_id' => [
                'title' => trans('plugins/ecommerce::order.customer_label'),
                'class' => 'text-start',
            ],
            'amount' => [
                'title' => trans('plugins/ecommerce::order.amount'),
            ],
        ];

        if (EcommerceHelper::isTaxEnabled()) {
            $columns['tax_amount'] = [
                'title' => trans('plugins/ecommerce::order.tax_amount'),
            ];
        }

        $columns += [
            'shipping_amount' => [
                'title' => trans('plugins/ecommerce::order.shipping_amount'),
            ],
            'payment_method' => [
                'name' => 'payment_id',
                'title' => trans('plugins/ecommerce::order.payment_method'),
            ],
            'payment_status' => [
                'name' => 'payment_id',
                'title' => trans('plugins/ecommerce::order.payment_status_label'),
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
            ],
            CreatedAtColumn::make(),
        ];

        return $columns;
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
