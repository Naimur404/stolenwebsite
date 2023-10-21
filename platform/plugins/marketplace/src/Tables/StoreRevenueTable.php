<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Models\Revenue;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class StoreRevenueTable extends TableAbstract
{
    protected ?int $customerId;

    public function setup(): void
    {
        $this->model(Revenue::class);

        $this->setCustomerId(request()->route()->parameter('id'));
        $this->pageLength = 10;
        $this->type = self::TABLE_TYPE_SIMPLE;
        $this->view = 'core/table::simple-table';
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('amount', function (Revenue $item) {
                return Html::tag('span', ($item->sub_amount < 0 ? '-' : '') . format_price($item->amount), ['class' => 'text-success']);
            })
            ->editColumn('sub_amount', function (Revenue $item) {
                return ($item->sub_amount < 0 ? '-' : '') . format_price($item->sub_amount);
            })
            ->editColumn('fee', function (Revenue $item) {
                return Html::tag('span', ($item->fee < 0 ? '-' : '') . format_price($item->fee), ['class' => 'text-danger']);
            })
            ->editColumn('order_id', function (Revenue $item) {
                if (! $item->order->id) {
                    return BaseHelper::clean($item->description);
                }

                $url = '';
                if (is_in_admin(true)) {
                    if ($this->hasPermission('orders.edit')) {
                        $url = route('orders.edit', $item->order->id);
                    }
                } else {
                    $url = route('marketplace.vendor.orders.edit', $item->order->id);
                }

                return $url ? Html::link($url, $item->order->code, ['target' => '_blank']) : $item->order->code;
            })
            ->editColumn('type', function (Revenue $item) {
                return $item->type->toHtml();
            })
            ->filterColumn('id', function (Builder $query, $keyword) {
                if ($keyword) {
                    $query->where('id', $keyword);
                }
            })
            ->filterColumn('order_id', function (Builder $query, $keyword) {
                if ($keyword) {
                    $query
                        ->where('order_id', $keyword)
                        ->orWhereHas('order', fn (Builder $query) => $query->where('code', 'like', '%' . $keyword));
                }
            })
            ->filterColumn('type', function (Builder $query, $keyword) {
                if ($keyword && in_array($keyword, RevenueTypeEnum::values())) {
                    $query->where('type', $keyword);
                }
            });

        if (! $this->customerId) {
            $data
                ->editColumn('customer_id', function (Revenue $item) {
                    if (! $item->customer->id || ! $item->customer->store->id) {
                        return '&mdash;';
                    }

                    $store = $item->customer->store;
                    $logo = Html::image($store->logo_url, $store->name, ['width' => 20, 'class' => 'rounded me-2']);
                    $storeName = $store->name;
                    if (is_in_admin(true) && $this->hasPermission('marketplace.store.view')) {
                        $storeName = Html::link(route('marketplace.store.view', $store->id), $storeName);
                    }

                    return BaseHelper::clean($logo . $storeName);
                });
        }

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'sub_amount',
                'fee',
                'amount',
                'order_id',
                'created_at',
                'type',
                'description',
            ])
            ->with(['order:id,code'])
            ->when($this->customerId, function (Builder $query) {
                $query
                    ->where('customer_id', $this->customerId)
                    ->with([
                        'customer:id,name,avatar',
                        'customer.store:id,name,logo,customer_id',
                    ])
                    ->addSelect('customer_id');
            });

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [
            IdColumn::make(),
            'order_id' => [
                'title' => trans('plugins/ecommerce::order.description'),
                'class' => 'text-start',
            ],
        ];

        if (! $this->customerId) {
            $columns['customer_id'] = [
                'title' => 'store',
                'class' => 'text-start',
            ];
        }

        return array_merge($columns, [
            'fee' => [
                'title' => trans('plugins/ecommerce::shipping.fee'),
                'class' => 'text-start',
            ],
            'sub_amount' => [
                'title' => trans('plugins/ecommerce::order.sub_amount'),
                'class' => 'text-start',
            ],
            'amount' => [
                'title' => trans('plugins/ecommerce::order.amount'),
                'class' => 'text-start',
            ],
            'type' => [
                'title' => trans('plugins/marketplace::revenue.forms.type'),
                'class' => 'text-start',
            ],
            CreatedAtColumn::make(),
        ]);
    }

    public function setCustomerId(int|string|null $customerId): self
    {
        $this->customerId = $customerId;
        $this->setOption('id', $this->getOption('id') . $this->customerId);

        return $this;
    }

    protected function getDom(): ?string
    {
        if ($this->type == self::TABLE_TYPE_ADVANCED) {
            return "fBrt<'datatables__info_wrap'pli<'clearfix'>>";
        }

        return "rt<'datatables__info_wrap'pli<'clearfix'>>";
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }

    public function htmlDrawCallbackFunction(): ?string
    {
        return parent::htmlDrawCallbackFunction() . '$("[data-bs-toggle=tooltip]").tooltip({placement: "top", boundary: "window"});';
    }
}
