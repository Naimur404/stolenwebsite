<?php

namespace Botble\ACL\Tables;

use Botble\ACL\Models\Role;
use Botble\Base\Facades\BaseHelper;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class RoleTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Role::class)
            ->addActions([
                EditAction::make()->route('roles.edit'),
                DeleteAction::make()->route('roles.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('created_by', function (Role $item) {
                return BaseHelper::clean($item->author->name);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with('author')
            ->select([
                'id',
                'name',
                'description',
                'created_at',
                'created_by',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('roles.edit'),
            Column::make('description')
                ->title(trans('core/base::tables.description'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            Column::make('created_by')
                ->title(trans('core/acl::permissions.created_by'))
                ->width(100),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('roles.create'), 'roles.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('roles.destroy'),
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
        ];
    }
}
