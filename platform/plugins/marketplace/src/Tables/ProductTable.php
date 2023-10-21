<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Exports\ProductExport;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ProductTable extends TableAbstract
{
    public function setup(): void
    {
        $this->exportClass = ProductExport::class;

        $this
            ->model(Product::class)
            ->addActions([
                EditAction::make()->route('marketplace.vendor.products.edit'),
                DeleteAction::make()->route('marketplace.vendor.products.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                return Html::link(route('marketplace.vendor.products.edit', $item->id), BaseHelper::clean($item->name));
            })
            ->editColumn('image', function ($item) {
                return $this->displayThumbnail($item->image);
            })
            ->editColumn('price', function ($item) {
                return $item->price_in_table;
            })
            ->editColumn('quantity', function ($item) {
                return $item->with_storehouse_management ? $item->quantity : '&#8734;';
            })
            ->editColumn('sku', function ($item) {
                return BaseHelper::clean($item->sku ?: '&mdash;');
            })
            ->editColumn('order', function ($item) {
                return (string) $item->order;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'id',
                'name',
                'order',
                'created_at',
                'status',
                'sku',
                'images',
                'price',
                'sale_price',
                'sale_type',
                'start_date',
                'end_date',
                'quantity',
                'with_storehouse_management',
                'product_type',
            ])
            ->where('is_variation', 0)
            ->where('store_id', auth('customer')->user()->store->id);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make(),
            NameColumn::make(),
            'price' => [
                'title' => trans('plugins/ecommerce::products.price'),
                'class' => 'text-start',
            ],
            'quantity' => [
                'title' => trans('plugins/ecommerce::products.quantity'),
                'class' => 'text-start',
            ],
            'sku' => [
                'title' => trans('plugins/ecommerce::products.sku'),
                'class' => 'text-start',
            ],
            'order' => [
                'title' => trans('core/base::tables.order'),
                'width' => '50px',
            ],
            CreatedAtColumn::make(),
            'status' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
            ],
        ];
    }

    public function buttons(): array
    {
        if (EcommerceHelper::isEnabledSupportDigitalProducts()) {
            $buttons['create'] = [
                'extend' => 'collection',
                'text' => view('core/table::partials.create')->render(),
                'buttons' => [
                    [
                        'className' => 'action-item',
                        'text' => ProductTypeEnum::PHYSICAL()->toIcon() . ' ' . Html::tag('span', ProductTypeEnum::PHYSICAL()->label(), [
                            'data-action' => 'physical-product',
                            'data-href' => route('marketplace.vendor.products.create'),
                            'class' => 'ms-1',
                        ])->toHtml(),
                    ],
                    [
                        'className' => 'action-item',
                        'text' => ProductTypeEnum::DIGITAL()->toIcon() . ' ' . Html::tag('span', ProductTypeEnum::DIGITAL()->label(), [
                            'data-action' => 'digital-product',
                            'data-href' => route('marketplace.vendor.products.create', ['product_type' => 'digital']),
                            'class' => 'ms-1',
                        ])->toHtml(),
                    ],
                ],
            ];
        } else {
            $buttons = $this->addCreateButton(route('marketplace.vendor.products.create'));
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::class,
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
            'order' => [
                'title' => trans('core/base::tables.order'),
                'type' => 'number',
                'validate' => 'required|min:0',
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

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
