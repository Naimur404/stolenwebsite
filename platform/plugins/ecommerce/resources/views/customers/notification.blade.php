@if(! $data->confirmed_at)
    <div class="note note-warning">
        <p>
            {!! BaseHelper::clean(trans('plugins/ecommerce::customer.verify_email.notification', [
                'approve_link' => Html::link(route('customers.verify-email', $data->id),
                trans('plugins/ecommerce::customer.verify_email.approve_here'),
                ['class' => 'verify-customer-email-button']),
            ])) !!}
        </p>
    </div>

    @push('footer')
        <x-core-base::modal
            id="verify-customer-email-modal"
            :title="trans('plugins/ecommerce::customer.verify_email.confirm_heading')"
            button-id="confirm-verify-customer-email-button"
            :button-label="trans('plugins/ecommerce::customer.verify_email.confirm_button')"
            type="warning"
        >
            {!! trans('plugins/ecommerce::customer.verify_email.confirm_description') !!}
        </x-core-base::modal>
    @endpush
@endif
