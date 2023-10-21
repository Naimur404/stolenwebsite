<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Customer;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class UnverifiedVendorTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Customer::class)
            ->addActions([
                Action::make('view')
                    ->route('marketplace.unverified-vendors.view')
                    ->permission('marketplace.unverified-vendors.index')
                    ->label(__('View'))
                    ->icon('fas fa-eye'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                if (! $this->hasPermission('marketplace.unverified-vendors.edit')) {
                    return BaseHelper::clean($item->name);
                }

                return Html::link(route('marketplace.unverified-vendors.view', $item->id), BaseHelper::clean($item->name));
            })
            ->editColumn('avatar', function ($item) {
                if ($this->request()->input('action') == 'excel' ||
                    $this->request()->input('action') == 'csv') {
                    return $item->avatar_url;
                }

                return Html::tag('img', '', ['src' => $item->avatar_url, 'alt' => BaseHelper::clean($item->name), 'width' => 50]);
            })
            ->editColumn('store_name', function ($item) {
                return BaseHelper::clean($item->store->name);
            })
            ->editColumn('store_phone', function ($item) {
                return BaseHelper::clean($item->store->phone);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'id',
                'name',
                'created_at',
                'is_vendor',
                'avatar',
            ])
            ->where([
                'is_vendor' => true,
                'vendor_verified_at' => null,
            ])
            ->with(['store']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            'avatar' => [
                'title' => trans('plugins/ecommerce::customer.avatar'),
            ],
            NameColumn::make(),
            'store_name' => [
                'title' => trans('plugins/marketplace::unverified-vendor.forms.store_name'),
                'class' => 'text-start',
                'searchable' => false,
                'orderable' => false,
            ],
            'store_phone' => [
                'title' => trans('plugins/marketplace::unverified-vendor.forms.store_phone'),
                'class' => 'text-start',
                'searchable' => false,
                'orderable' => false,
            ],
            CreatedAtColumn::make(),
        ];
    }
}
