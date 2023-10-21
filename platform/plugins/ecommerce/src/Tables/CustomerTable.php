<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CustomerTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Customer::class)
            ->addActions([
                EditAction::make()->route('customers.edit'),
                DeleteAction::make()->route('customers.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('avatar', function (Customer $item) {
                if ($this->request()->input('action') == 'excel' ||
                    $this->request()->input('action') == 'csv') {
                    return $item->avatar_url;
                }

                return Html::tag(
                    'img',
                    '',
                    ['src' => $item->avatar_url, 'alt' => BaseHelper::clean($item->name), 'width' => 50]
                );
            })
            ->editColumn('name', function (Customer $item) {
                if (! $this->hasPermission('customers.edit')) {
                    return BaseHelper::clean($item->name);
                }

                return Html::link(route('customers.edit', $item->id), BaseHelper::clean($item->name));
            })
            ->editColumn('email', function (Customer $item) {
                return BaseHelper::clean($item->email);
            });

        if (EcommerceHelper::isEnableEmailVerification()) {
            $data = $data
                ->addColumn('confirmed_at', function (Customer $item) {
                    return $item->confirmed_at ? Html::tag(
                        'span',
                        trans('core/base::base.yes'),
                        ['class' => 'text-success']
                    ) : trans('core/base::base.no');
                });
        }

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
                'email',
                'avatar',
                'created_at',
                'status',
                'confirmed_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [
            IdColumn::make(),
            Column::make('avatar')
                ->title(trans('plugins/ecommerce::customer.avatar')),
            NameColumn::make(),
            Column::make('email')
                ->title(trans('plugins/ecommerce::customer.email'))
                ->alignLeft(),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];

        if (EcommerceHelper::isEnableEmailVerification()) {
            $columns += [
                'confirmed_at' => [
                    'title' => trans('plugins/ecommerce::customer.email_verified'),
                    'width' => '100px',
                ],
            ];
        }

        return $columns;
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('customers.create'), 'customers.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('customers.destroy'),
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
            'email' => [
                'title' => trans('core/base::tables.email'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => CustomerStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', CustomerStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->isEmpty()) {
            return view('plugins/ecommerce::customers.intro');
        }

        return parent::renderTable($data, $mergeData);
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
