<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class ShippingRuleItemTable extends TableAbstract
{
    protected array $countries;

    protected bool $isExporting;

    public function setup(): void
    {
        $this
            ->model(ShippingRule::class)
            ->addActions([
                EditAction::make()->route('ecommerce.shipping-rule-items.edit'),
                DeleteAction::make()->route('ecommerce.shipping-rule-items.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('shipping_rule_id', function (ShippingRule $item) {
                return $item->shippingRule->name;
            })
            ->editColumn('country', function (ShippingRule $item) {
                return Arr::get(
                    $this->countries,
                    $item->shippingRule->shipping->country
                ) ?: $item->shippingRule->shipping->country;
            });
        if ($this->isExporting) {
            $data = $data->editColumn('is_enabled', function (ShippingRule $item) {
                if ($item->is_enabled) {
                    return trans('core/base::base.yes');
                }

                return trans('core/base::base.no');
            });
        } else {
            $data = $data->editColumn('country', function (ShippingRule $item) {
                return Arr::get(
                    $this->countries,
                    $item->shippingRule->shipping->country
                ) ?: $item->shippingRule->shipping->country;
            })
                ->editColumn('state', function (ShippingRule $item) {
                    return $item->state_name;
                })
                ->editColumn('city', function (ShippingRule $item) {
                    return $item->city_name;
                })
                ->editColumn('adjustment_price', function (ShippingRule $item) {
                    return ($item->adjustment_price < 0 ? '-' : '') .
                        format_price($item->adjustment_price) .
                        Html::tag(
                            'small',
                            '(' . format_price(max($item->adjustment_price + $item->shippingRule->price, 0)) . ')',
                            ['class' => 'text-info ms-1']
                        );
                })
                ->editColumn('is_enabled', function (ShippingRule $item) {
                    if ($item->is_enabled) {
                        return Html::tag('span', trans('core/base::base.yes'), ['class' => 'text-primary']);
                    }

                    return Html::tag('span', trans('core/base::base.no'), ['class' => 'text-secondary']);
                });
        }

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->with(['shippingRule', 'shippingRule.shipping'])
            ->select([
                'id',
                'shipping_rule_id',
                'country',
                'state',
                'city',
                'adjustment_price',
                'is_enabled',
                'zip_code',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            'shipping_rule_id' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.tables.shipping_rule'),
            ],
            'country' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.tables.country'),
            ],
            'state' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.tables.state'),
            ],
            'city' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.tables.city'),
            ],
            'zip_code' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.tables.zip_code'),
            ],
            'adjustment_price' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.tables.adjustment_price'),
            ],
            'is_enabled' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.tables.is_enabled'),
                'width' => '40',
            ],
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('ecommerce.shipping-rule-items.create'), 'shipping_methods.index');

        if ($this->hasPermission('ecommerce.shipping-rule-items.bulk-import')) {
            $buttons['import'] = [
                'link' => route('ecommerce.shipping-rule-items.bulk-import.index'),
                'text' => '<i class="fas fa-file-import"></i> ' . trans('plugins/ecommerce::bulk-import.tables.import'),
            ];
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('shipping_methods.index'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'adjustment_price' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.forms.adjustment_price'),
                'type' => 'number',
                'validate' => 'required|numeric',
            ],
            'is_enabled' => [
                'title' => trans('plugins/ecommerce::shipping.rule.item.forms.is_enabled'),
                'type' => 'select',
                'choices' => [
                    '1' => trans('core/base::base.yes'),
                    '0' => trans('core/base::base.no'),
                ],
                'validate' => 'required|in:0,1',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function getDefaultButtons(): array
    {
        $buttons = parent::getDefaultButtons();

        return array_merge($buttons, ['export']);
    }
}
