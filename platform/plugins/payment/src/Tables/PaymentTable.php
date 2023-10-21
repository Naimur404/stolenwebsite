<?php

namespace Botble\Payment\Tables;

use Botble\Base\Facades\Html;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PaymentTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Payment::class)
            ->addActions([
                EditAction::make()->route('payment.show'),
                DeleteAction::make()->route('payment.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('charge_id', function (Payment $item) {
                return Html::link(route('payment.show', $item->getKey()), Str::limit($item->charge_id, 20));
            })
            ->editColumn('customer_id', function (Payment $item) {
                if ($item->customer_id && $item->customer_type && class_exists($item->customer_type)) {
                    return $item->customer->name;
                }

                if ($item->order && $item->order->address) {
                    return $item->order->address->name;
                }

                return '&mdash;';
            })
            ->editColumn('payment_channel', function (Payment $item) {
                return $item->payment_channel->label();
            })
            ->editColumn('amount', function (Payment $item) {
                return $item->amount . ' ' . $item->currency;
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
                'charge_id',
                'amount',
                'currency',
                'payment_channel',
                'created_at',
                'status',
                'order_id',
                'customer_id',
                'customer_type',
            ])->with(['customer']);

        if (method_exists($query->getModel(), 'order')) {
            $query->with(['customer', 'order']);
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('charge_id')
                ->title(trans('plugins/payment::payment.charge_id')),
            Column::make('customer_id')
                ->title(trans('plugins/payment::payment.payer_name'))
                ->alignLeft(),
            Column::make('amount')
                ->title(trans('plugins/payment::payment.amount'))
                ->alignLeft(),
            Column::make('payment_channel')
                ->title(trans('plugins/payment::payment.payment_channel'))
                ->alignLeft(),
            StatusColumn::make(),
            CreatedAtColumn::make(),
        ];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('payment.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'customSelect',
                'choices' => PaymentStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', PaymentStatusEnum::values()),
            ],
            'charge_id' => [
                'title' => trans('plugins/payment::payment.charge_id'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function saveBulkChangeItem(Model|Payment $item, string $inputKey, ?string $inputValue): Model|bool
    {
        if ($inputKey === 'status') {
            $request = request();

            $request->merge(['status' => $inputValue]);

            do_action(ACTION_AFTER_UPDATE_PAYMENT, $request, $item);
        }

        return parent::saveBulkChangeItem($item, $inputKey, $inputValue);
    }
}
