<?php

namespace Botble\SimpleSlider\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\SimpleSlider\Models\SimpleSlider;
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
use Illuminate\Validation\Rule;

class SimpleSliderTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(SimpleSlider::class)
            ->addActions([
                EditAction::make()->route('simple-slider.edit'),
                DeleteAction::make()->route('simple-slider.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('key', function (SimpleSlider $item) {
                return shortcode()->generateShortcode('simple-slider', ['key' => $item->key]);
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
                'key',
                'status',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('simple-slider.edit'),
            Column::make('key')
                ->title(trans('plugins/simple-slider::simple-slider.key'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('simple-slider.create'), 'simple-slider.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('simple-slider.destroy'),
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
            'key' => [
                'title' => trans('plugins/simple-slider::simple-slider.key'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'customSelect',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|' . Rule::in(BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
