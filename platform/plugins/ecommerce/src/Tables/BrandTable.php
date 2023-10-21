<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Brand;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BrandTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Brand::class)
            ->addActions([
                EditAction::make()->route('brands.edit'),
                DeleteAction::make()->route('brands.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Brand $item) {
                if (! $this->hasPermission('brands.edit')) {
                    return BaseHelper::clean($item->name);
                }

                return Html::link(route('brands.edit', $item->id), BaseHelper::clean($item->name));
            })
            ->editColumn('logo', function (Brand $item) {
                return $this->displayThumbnail($item->logo);
            })
            ->editColumn('is_featured', function (Brand $item) {
                return $item->is_featured ? trans('core/base::base.yes') : trans('core/base::base.no');
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'created_at',
                'status',
                'is_featured',
                'logo',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make(),
            Column::make('logo')
                ->title(trans('plugins/ecommerce::brands.logo'))
                ->alignLeft(),
            Column::make('is_featured')
                ->title(trans('core/base::tables.is_featured'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('brands.create'), 'brands.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('brands.destroy'),
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

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->isEmpty()) {
            return view('plugins/ecommerce::brands.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
}
