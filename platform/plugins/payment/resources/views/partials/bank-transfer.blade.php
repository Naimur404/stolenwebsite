<li class="list-group-item">
    <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_bank_transfer"
           @if (PaymentMethods::getSelectingMethod() == \Botble\Payment\Enums\PaymentMethodEnum::BANK_TRANSFER) checked @endif
           value="bank_transfer">
    <label for="payment_bank_transfer" class="text-start">{{ setting('payment_bank_transfer_name', trans('plugins/payment::payment.payment_via_bank_transfer')) }}</label>
    <div class="payment_bank_transfer_wrap payment_collapse_wrap collapse @if (PaymentMethods::getSelectingMethod() == \Botble\Payment\Enums\PaymentMethodEnum::BANK_TRANSFER) show @endif" style="padding: 15px 0;">
        {!! BaseHelper::clean(setting('payment_bank_transfer_description')) !!}
    </div>
</li>
