<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Marketplace\Models\Store;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class StoreTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Store::class)
            ->addActions([
                Action::make('view')
                    ->route('marketplace.store.view')
                    ->permission('marketplace.store.view')
                    ->label(trans('plugins/marketplace::store.view'))
                    ->icon('fas fa-eye'),
                EditAction::make()->route('marketplace.store.edit'),
                DeleteAction::make()->route('marketplace.store.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                if (! $this->hasPermission('marketplace.store.edit')) {
                    return BaseHelper::clean($item->name);
                }

                return Html::link(route('marketplace.store.edit', $item->id), BaseHelper::clean($item->name));
            })
            ->editColumn('logo', function ($item) {
                return Html::image(
                    RvMedia::getImageUrl($item->logo, 'thumb', false, RvMedia::getDefaultImage()),
                    BaseHelper::clean($item->name),
                    ['width' => 50]
                );
            })
            ->editColumn('earnings', function ($item) {
                return $item->customer->id ? format_price($item->customer->balance ?: 0) : '--';
            })
            ->editColumn('products_count', function ($item) {
                return $item->products_count;
            })
            ->addColumn('customer_name', function ($item) {
                if (! $item->customer->name) {
                    return '&mdash;';
                }

                return Html::link(route('customers.edit', $item->customer->id), $item->customer->name);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'id',
                'logo',
                'name',
                'created_at',
                'status',
                'customer_id',
            ])
            ->with(['customer', 'customer.vendorInfo'])
            ->withCount(['products']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            'logo' => [
                'title' => trans('plugins/marketplace::store.forms.logo'),
                'width' => '70px',
            ],
            NameColumn::make(),
            'earnings' => [
                'title' => trans('plugins/marketplace::marketplace.tables.earnings'),
                'class' => 'text-start',
                'searchable' => false,
                'orderable' => false,
                'width' => '100px',
            ],
            'products_count' => [
                'title' => trans('plugins/marketplace::marketplace.tables.products_count'),
                'searchable' => false,
                'orderable' => false,
            ],
            'customer_name' => [
                'title' => trans('plugins/marketplace::marketplace.vendor'),
                'class' => 'text-start',
                'orderable' => false,
                'searchable' => false,
                'exportable' => false,
                'printable' => false,
            ],
            CreatedAtColumn::make(),
            'status' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
            ],
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.store.create'), 'marketplace.store.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('marketplace.store.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function getOperationsHeading(): array
    {
        return [
            'operations' => [
                'title' => trans('core/base::tables.operations'),
                'width' => '180px',
                'class' => 'text-end',
                'orderable' => false,
                'searchable' => false,
                'exportable' => false,
                'printable' => false,
            ],
        ];
    }
}
