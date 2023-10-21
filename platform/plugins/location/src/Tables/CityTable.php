<?php

namespace Botble\Location\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Html;
use Botble\Location\Models\City;
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

class CityTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(City::class)
            ->addActions([
                EditAction::make()->route('city.edit'),
                DeleteAction::make()->route('city.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('state_id', function (City $item) {
                if (! $item->state_id || ! $item->state->name) {
                    return '&mdash;';
                }

                return Html::link(route('state.edit', $item->state_id), $item->state->name);
            })
            ->editColumn('country_id', function (City $item) {
                if (! $item->country_id || ! $item->country->name) {
                    return '&mdash;';
                }

                return Html::link(route('country.edit', $item->country_id), $item->country->name);
            })
            ->filter(function (Builder $query) {
                $keyword = $this->request->input('search.value');

                if (! $keyword) {
                    return $query;
                }

                return $query->where(function (Builder $query) use ($keyword) {
                    $query
                        ->where('id', $keyword)
                        ->orWhere('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhereHas('state', function (Builder $subQuery) use ($keyword) {
                            return $subQuery
                                ->where('name', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('country', function (Builder $subQuery) use ($keyword) {
                            return $subQuery
                                ->where('name', 'LIKE', '%' . $keyword . '%');
                        });
                });
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
                'state_id',
                'country_id',
                'created_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('city.edit'),
            Column::make('state_id')
                ->title(trans('plugins/location::city.state'))
                ->alignLeft(),
            Column::make('country_id')
                ->title(trans('plugins/location::city.country'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('city.create'), 'city.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('city.destroy'),
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
            'state_id' => [
                'title' => trans('plugins/location::city.state'),
                'type' => 'customSelect',
                'validate' => 'required|max:120',
            ],
            'country_id' => [
                'title' => trans('plugins/location::city.country'),
                'type' => 'customSelect',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'customSelect',
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
