<?php

namespace Botble\AuditLog\Tables;

use Botble\AuditLog\Models\AuditHistory;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AuditLogTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(AuditHistory::class)
            ->addActions([
                DeleteAction::make()->route('audit-log.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('action', function (AuditHistory $item) {
                return view('plugins/audit-log::activity-line', ['history' => $item])->render();
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with(['user'])
            ->select(['*']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('action')
                ->title(trans('plugins/audit-log::history.action'))
                ->alignLeft(),
        ];
    }

    public function buttons(): array
    {
        return [
            'empty' => [
                'link' => route('audit-log.empty'),
                'text' => Html::tag('i', '', ['class' => 'fa fa-trash'])->toHtml() . ' ' . trans(
                    'plugins/audit-log::history.delete_all'
                ),
            ],
        ];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('audit-log.destroy'),
        ];
    }
}
