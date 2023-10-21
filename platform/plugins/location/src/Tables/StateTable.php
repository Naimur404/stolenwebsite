<?php

namespace Botble\Location\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Html;
use Botble\Location\Models\State;
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

class StateTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(State::class)
            ->addActions([
                EditAction::make()->route('state.edit'),
                DeleteAction::make()->route('state.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('country_id', function (State $item) {
                if (! $item->country_id && $item->country->name) {
                    return null;
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
            NameColumn::make()->route('state.edit'),
            Column::make('country_id')
                ->title(trans('plugins/location::state.country'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('state.create'), 'state.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('state.destroy'),
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
            'country_id' => [
                'title' => trans('plugins/location::state.country'),
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
