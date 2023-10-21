<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Invoice;
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

class InvoiceTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Invoice::class)
            ->addActions([
                EditAction::make()->route('ecommerce.invoice.edit'),
                DeleteAction::make()->route('ecommerce.invoice.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('customer_name', function (Invoice $item) {
                return $item->customer_name;
            })
            ->editColumn('amount', function (Invoice $item) {
                return format_price($item->amount);
            })
            ->editColumn('code', function (Invoice $item) {
                if (! $this->hasPermission('ecommerce.invoice.edit')) {
                    return $item->code;
                }

                return Html::link(route('ecommerce.invoice.edit', $item->id), $item->code);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'customer_name',
                'code',
                'amount',
                'created_at',
                'updated_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('customer_name')
                ->title(trans('core/base::tables.name'))
                ->alignLeft(),
            Column::make('code')
                ->title(trans('plugins/ecommerce::invoice.table.code'))
                ->alignLeft(),
            Column::make('amount')
                ->title(trans('plugins/ecommerce::invoice.table.amount'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        $buttons = [];

        if ($this->hasPermission('ecommerce.invoice.edit')) {
            $buttons['generate-invoices'] = [
                'link' => route('ecommerce.invoice.generate-invoices'),
                'text' => '<i class="fas fa-file-export"></i> ' . trans('plugins/ecommerce::invoice.generate_invoices'),
                'class' => 'btn-info',
            ];
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('ecommerce.invoice.destroy'),
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
