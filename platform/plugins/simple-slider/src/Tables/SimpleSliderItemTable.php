<?php

namespace Botble\SimpleSlider\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class SimpleSliderItemTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(SimpleSliderItem::class)
            ->addActions([
                EditAction::make()
                    ->route('simple-slider-item.edit')
                    ->attributes(['data-fancybox', 'data-type' => 'ajax']),
                DeleteAction::make()
                    ->route('simple-slider-item.destroy.get')
                    ->permission('simple-slider-item.destroy')
                    ->attributes(['data-fancybox', 'data-type' => 'ajax']),
            ]);

        $this->view = 'plugins/simple-slider::items';
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('image', function (SimpleSliderItem $item) {
                return view('plugins/simple-slider::partials.thumbnail', compact('item'))->render();
            })
            ->editColumn('title', function (SimpleSliderItem $item) {
                $name = BaseHelper::clean($item->title);

                if (! $this->hasPermission('simple-slider-item.edit')) {
                    return $name;
                }

                return Html::link('#', $name, [
                    'data-fancybox' => true,
                    'data-type' => 'ajax',
                    'data-src' => route('simple-slider-item.edit', $item->getKey()),
                ]);
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
                'title',
                'image',
                'order',
                'created_at',
            ])
            ->orderBy('order')
            ->where('simple_slider_id', request()->route()->parameter('id'));

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make(),
            Column::make('title')
                ->title(trans('core/base::tables.title'))
                ->alignLeft(),
            Column::make('order')
                ->title(trans('core/base::tables.order'))
                ->className('text-start order-column'),
            CreatedAtColumn::make(),
        ];
    }

    protected function getDom(): string|null
    {
        return $this->simpleDom();
    }
}
