<?php

namespace Botble\Faq\Tables;

use Botble\Base\Facades\Html;
use Botble\Faq\Models\Faq;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class FaqTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Faq::class)
            ->addActions([
                EditAction::make()->route('faq.edit'),
                DeleteAction::make()->route('faq.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('question', function (Faq $item) {
                if (! $this->hasPermission('faq.edit')) {
                    return $item->question;
                }

                return Html::link(route('faq.edit', $item->getKey()), $item->question);
            })
            ->editColumn('category_id', function (Faq $item) {
                return $item->category->name;
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
                'question',
                'created_at',
                'answer',
                'category_id',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('question')
                ->title(trans('plugins/faq::faq.question'))
                ->alignLeft(),
            Column::make('category_id')
                ->title(trans('plugins/faq::faq.category'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('faq.create'), 'faq.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('faq.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'question' => [
                'title' => trans('plugins/faq::faq.question'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
