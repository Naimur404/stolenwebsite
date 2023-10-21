<?php

namespace Botble\Marketplace\Tables;

use Botble\Ecommerce\Tables\CustomerTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class VendorTable extends CustomerTable
{
    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'id',
                'name',
                'email',
                'avatar',
                'created_at',
                'status',
                'confirmed_at',
            ])
            ->where('is_vendor', true)
            ->with(['store']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = parent::columns();

        $columns['store_name'] = [
            'title' => trans('plugins/marketplace::marketplace.store_name'),
            'class' => 'text-start',
            'orderable' => false,
            'searchable' => false,
            'exportable' => false,
            'printable' => false,
        ];

        return $columns;
    }
}
