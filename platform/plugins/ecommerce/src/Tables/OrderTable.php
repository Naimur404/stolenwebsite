<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Order::class)
            ->addActions([
                EditAction::make()->route('orders.edit'),
                DeleteAction::make()->route('orders.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('payment_status', function (Order $item) {
                if (! is_plugin_active('payment')) {
                    return '&mdash;';
                }

                return $item->payment->status->label() ? BaseHelper::clean(
                    $item->payment->status->toHtml()
                ) : '&mdash;';
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
            })
            ->editColumn('customer_email', function (Order $item) {
                return BaseHelper::clean($item->user->email ?: $item->address->email);
            })
            ->editColumn('customer_phone', function (Order $item) {
                return BaseHelper::clean($item->user->phone ?: $item->address->phone);
            });

        if (EcommerceHelper::isTaxEnabled()) {
            $data = $data->editColumn('tax_amount', function (Order $item) {
                return format_price($item->tax_amount);
            });
        }

        $data = $data
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
        $with = ['user'];

        if (is_plugin_active('payment')) {
            $with[] = 'payment';
        }

        $query = $this
            ->getModel()
            ->query()
            ->with($with)
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
            ->where('is_finished', 1);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [
            IdColumn::make(),
            Column::make('user_id')
                ->title(trans('plugins/ecommerce::order.customer_label'))
                ->alignLeft(),
            Column::make('customer_email')
                ->title(trans('plugins/ecommerce::order.email'))
                ->alignLeft()
                ->orderable(false),
            Column::make('customer_phone')
                ->title(trans('plugins/ecommerce::order.phone'))
                ->alignLeft()
                ->orderable(false),
            Column::make('amount')
                ->title(trans('plugins/ecommerce::order.amount')),
        ];

        if (EcommerceHelper::isTaxEnabled()) {
            $columns[] = Column::make('tax_amount')
                ->title(trans('plugins/ecommerce::order.tax_amount'));
        }

        $columns += [
            Column::make('shipping_amount')
                ->title(trans('plugins/ecommerce::order.shipping_amount')),
            Column::make('payment_method')
                ->name('payment_id')
                ->title(trans('plugins/ecommerce::order.payment_method'))
                ->alignLeft(),
            Column::make('payment_status')
                ->name('payment_id')
                ->title(trans('plugins/ecommerce::order.payment_status_label')),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];

        return $columns;
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('orders.create'), 'orders.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('orders.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => OrderStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', OrderStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function getFilters(): array
    {
        $filters = parent::getFilters();

        $filters = array_merge($filters, [
            'customer_name' => [
                'title' => trans('plugins/ecommerce::ecommerce.customer_name'),
                'type' => 'text',
            ],
            'customer_email' => [
                'title' => trans('plugins/ecommerce::ecommerce.customer_email'),
                'type' => 'text',
            ],
            'customer_phone' => [
                'title' => trans('plugins/ecommerce::ecommerce.customer_phone'),
                'type' => 'text',
            ],
            'amount' => [
                'title' => trans('plugins/ecommerce::order.amount'),
                'type' => 'number',
            ],
            'shipping_method' => [
                'title' => trans('plugins/ecommerce::ecommerce.shipping_method'),
                'type' => 'select',
                'choices' => array_filter(ShippingMethodEnum::labels()),
            ],
        ]);

        if (is_plugin_active('payment')) {
            $filters = array_merge($filters, [
                'payment_method' => [
                    'title' => trans('plugins/ecommerce::order.payment_method'),
                    'type' => 'select',
                    'choices' => PaymentMethodEnum::labels(),
                ],
                'payment_status' => [
                    'title' => trans('plugins/ecommerce::order.payment_status_label'),
                    'type' => 'select',
                    'choices' => PaymentStatusEnum::labels(),
                ],
            ]);
        }

        if (is_plugin_active('marketplace')) {
            $filters['store_id'] = [
                'title' => trans('plugins/marketplace::store.forms.store'),
                'type' => 'select-search',
                'choices' => [-1 => theme_option('site_title')] + DB::table('mp_stores')->pluck('name', 'id')->all(),
            ];
        }

        return $filters;
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->isEmpty()) {
            return view('plugins/ecommerce::orders.intro');
        }

        return parent::renderTable($data, $mergeData);
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }

    public function saveBulkChangeItem(Model|Order $item, string $inputKey, string|null $inputValue): Model|bool
    {
        if ($inputKey === 'status' && $inputValue == OrderStatusEnum::CANCELED) {
            /**
             * @var Order $item
             */
            if (! $item->canBeCanceledByAdmin()) {
                return $item;
            }

            OrderHelper::cancelOrder($item);

            OrderHistory::query()->create([
                'action' => 'cancel_order',
                'description' => trans('plugins/ecommerce::order.order_was_canceled_by'),
                'order_id' => $item->id,
                'user_id' => Auth::id(),
            ]);

            return $item;
        }

        return parent::saveBulkChangeItem($item, $inputKey, $inputValue);
    }

    public function applyFilterCondition(
        Builder|QueryBuilder|Relation $query,
        string $key,
        string $operator,
        string|null $value
    ): Builder|QueryBuilder|Relation {
        switch ($key) {
            case 'customer_name':
                if (! $value) {
                    break;
                }

                return $this->filterByCustomer($query, 'name', $operator, $value);
            case 'customer_email':
                if (! $value) {
                    break;
                }

                return $this->filterByCustomer($query, 'email', $operator, $value);
            case 'customer_phone':
                if (! $value) {
                    break;
                }

                return $this->filterByCustomer($query, 'phone', $operator, $value);
            case 'status':
                if (! OrderStatusEnum::isValid($value)) {
                    return $query;
                }

                break;
            case 'shipping_method':
                if (! $value) {
                    break;
                }

                if (! ShippingMethodEnum::isValid($value)) {
                    return $query;
                }

                break;
            case 'payment_method':
                if (! is_plugin_active('payment') || ! PaymentMethodEnum::isValid($value)) {
                    return $query;
                }

                return $query->whereHas('payment', function ($subQuery) use ($value) {
                    $subQuery->where('payment_channel', $value);
                });

            case 'payment_status':
                if (! is_plugin_active('payment') || ! PaymentStatusEnum::isValid($value)) {
                    return $query;
                }

                return $query->whereHas('payment', function ($subQuery) use ($value) {
                    $subQuery->where('status', $value);
                });
            case 'store_id':
                if (! is_plugin_active('marketplace')) {
                    return $query;
                }
                if ($value == -1) {
                    return $query->where(function ($subQuery) {
                        $subQuery->whereNull('store_id')
                            ->orWhere('store_id', 0);
                    });
                }
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }

    protected function filterByCustomer(
        Builder|QueryBuilder|Relation $query,
        string $column,
        string $operator,
        string|null $value
    ): Builder|QueryBuilder|Relation {
        if ($operator === 'like') {
            $value = '%' . $value . '%';
        } elseif ($operator !== '=') {
            $operator = '=';
        }

        return $query
            ->where(function ($query) use ($column, $operator, $value) {
                $query
                    ->whereHas('user', function ($subQuery) use ($column, $operator, $value) {
                        $subQuery->where($column, $operator, $value);
                    })
                    ->orWhereHas('address', function ($subQuery) use ($column, $operator, $value) {
                        $subQuery->where($column, $operator, $value);
                    });
            });
    }
}
