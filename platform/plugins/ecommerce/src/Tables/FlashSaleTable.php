<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\FlashSale;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class FlashSaleTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(FlashSale::class)
            ->addActions([
                EditAction::make()->route('flash-sale.edit'),
                DeleteAction::make()->route('flash-sale.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (FlashSale $item) {
                if (! $this->hasPermission('flash-sale.edit')) {
                    return BaseHelper::clean($item->name);
                }

                return Html::link(route('flash-sale.edit', $item->id), BaseHelper::clean($item->name));
            })
            ->editColumn('end_date', function (FlashSale $item) {
                return Html::tag(
                    'span',
                    BaseHelper::formatDate($item->end_date),
                    ['class' => $item->expired ? 'text-danger' : 'text-success']
                );
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
                'name',
                'end_date',
                'created_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make(),
            Column::make('end_date')
                ->title(__('End date'))
                ->width(100),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('flash-sale.create'), 'flash-sale.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('flash-sale.destroy'),
        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
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
}
