<div class="alert alert-info mt-3">
    {!! BaseHelper::clean($bankInfo) !!}
    <br /><span>{!! BaseHelper::clean(__('Bank transfer amount: <strong>:amount</strong>', ['amount' => format_price($orderAmount)])) !!}</span>
    <br /><span>{!! BaseHelper::clean(__('Bank transfer description: <strong>Payment for order :code</strong>', ['code' => $orderCode])) !!}</span>
</div>
