<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Models\Discount;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\IdColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DiscountTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Discount::class)
            ->addActions([
                EditAction::make()->route('discounts.edit'),
                DeleteAction::make()->route('discounts.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('detail', function (Discount $item) {
                $isCoupon = $item->type === DiscountTypeEnum::COUPON;

                return view('plugins/ecommerce::discounts.detail', compact('item', 'isCoupon'))->render();
            })
            ->editColumn('total_used', function (Discount $item) {
                if ($item->type === DiscountTypeEnum::PROMOTION) {
                    return '&mdash;';
                }

                if ($item->quantity === null) {
                    return number_format($item->total_used);
                }

                return sprintf('%d/%d', number_format($item->total_used), number_format($item->quantity));
            })
            ->editColumn('start_date', function (Discount $item) {
                return BaseHelper::formatDate($item->start_date);
            })
            ->editColumn('end_date', function (Discount $item) {
                if (! $item->end_date) {
                    return '&mdash;';
                }

                return $item->end_date;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select(['*']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('detail')
                ->name('code')
                ->title(trans('plugins/ecommerce::discount.detail'))
                ->alignLeft(),
            Column::make('total_used')
                ->title(trans('plugins/ecommerce::discount.used'))
                ->width(100),
            Column::make('start_date')
                ->title(trans('plugins/ecommerce::discount.start_date')),
            Column::make('end_date')
                ->title(trans('plugins/ecommerce::discount.end_date')),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('discounts.create'), 'discounts.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('discounts.destroy'),
        ];
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->isEmpty()) {
            return view('plugins/ecommerce::discounts.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
}
