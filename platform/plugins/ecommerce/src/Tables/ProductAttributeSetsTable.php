<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductAttributeSetsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProductAttributeSet::class)
            ->addActions([
                EditAction::make()->route('product-attribute-sets.edit'),
                DeleteAction::make()->route('product-attribute-sets.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('title', function (ProductAttributeSet $item) {
                if (! $this->hasPermission('product-attribute-sets.edit')) {
                    return BaseHelper::clean($item->title);
                }

                return Html::link(route('product-attribute-sets.edit', $item->id), BaseHelper::clean($item->title));
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
                'created_at',
                'title',
                'slug',
                'order',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            'title' => [
                'title' => trans('core/base::tables.title'),
                'class' => 'text-start',
            ],
            'slug' => [
                'title' => trans('core/base::tables.slug'),
                'class' => 'text-start',
            ],
            'order' => [
                'title' => trans('core/base::tables.order'),
                'class' => 'text-start',
            ],
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('product-attribute-sets.create'), 'product-attribute-sets.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('product-attribute-sets.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'title' => [
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
            return view('plugins/ecommerce::product-attributes.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
}
