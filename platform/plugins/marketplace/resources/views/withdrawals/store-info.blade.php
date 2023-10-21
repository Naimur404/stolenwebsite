<div class="note note-warning approve-product-warning">
    <p>{!! BaseHelper::clean(trans('plugins/marketplace::store.withdrawal_approval_notification', [
        'vendor'  => Html::link(route('marketplace.store.view', $store->id), $store->name, ['target' => '_blank']),
        'balance' => format_price($store->customer->balance),
    ])) !!}</p>
</div>
