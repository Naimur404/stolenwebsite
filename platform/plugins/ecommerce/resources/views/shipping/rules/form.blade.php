<form action="{{ $rule ? route('shipping_methods.region.rule.update', $rule->id) : route('shipping_methods.region.rule.create') }}"
    method="{{ $rule ? 'PUT' : 'POST' }}">
    <div class="panel panel-default bg-aliceBlue content-box mb-0">
        <div class="panel-body">
            <div class="mb-3">
                <label class="text-title-field required">{{ trans('plugins/ecommerce::shipping.shipping_rule_name') }}</label>
                <input type="text" name="name" class="next-input input-sync-text-item"
                    data-target=".label-rule-item-name" value="{{ $rule ? $rule->name : null }}">
            </div>
            <div class="flexbox-grid-default">
                <div class="flexbox-content-no-padding">
                    <div class="mb-3">
                        <label class="text-title-field required">{{ trans('plugins/ecommerce::shipping.type') }}</label>
                        {!! Form::customSelect('type', ['' => trans('plugins/ecommerce::shipping.rule.select_type')] + \Botble\Ecommerce\Enums\ShippingRuleTypeEnum::availableLabels($rule ? $rule->shipping : null), $rule ? $rule->type : '', ['class' => 'select-rule-type'], \Botble\Ecommerce\Enums\ShippingRuleTypeEnum::toSelectAttributes()) !!}
                    </div>
                </div>
                <div class="flexbox-content-no-padding pl15 rule-from-to-inputs @if ($rule && ! $rule->type->showFromToInputs()) d-none @endif">
                    <div class="mb-3">
                        <label class="text-title-field rule-from-to-label">
                            {{ $rule ? $rule->type->label() : \Botble\Ecommerce\Enums\ShippingRuleTypeEnum::BASED_ON_PRICE()->label() }}
                        </label>
                        <div class="flexbox-grid-default flexbox-align-items-center">
                            <div class="flexbox-auto-content">
                                <div class="next-input--stylized">
                                    <span class="next-input-add-on next-input__add-on--before unit-item-label">
                                        {{ $rule ? $rule->type->toUnit() : \Botble\Ecommerce\Enums\ShippingRuleTypeEnum::BASED_ON_PRICE()->toUnit() }}
                                    </span>
                                    <input type="text" name="from"
                                        class="next-input input-mask-number next-input--invisible input-sync-item"
                                        data-target=".from-value-label"
                                        value="{{ $rule ? $rule->from : 0 }}">
                                </div>
                            </div>
                            <div class="flexbox-auto-left pl5 p-r5">
                                <span class="inline">—</span>
                            </div>
                            <div class="flexbox-auto-content">
                                <div class="next-input--stylized">
                                    <span class="next-input-add-on next-input__add-on--before unit-item-label">
                                        {{ $rule ? $rule->type->toUnit() : \Botble\Ecommerce\Enums\ShippingRuleTypeEnum::BASED_ON_PRICE()->toUnit() }}
                                    </span>
                                    <input type="text" name="to"
                                        class="next-input input-mask-number next-input--invisible input-sync-item input-to-value-field"
                                        data-target=".to-value-label"
                                        value="{{ $rule && $rule->to != 0 ? $rule->to : null }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flexbox-grid-default">
                <div class="flexbox-content-no-padding">
                    <div class="form-group mb-3">
                        <label class="text-title-field required">{{ trans('plugins/ecommerce::shipping.shipping_fee') }}</label>
                        <div class="next-input--stylized">
                            <span class="next-input-add-on next-input__add-on--before">{{ get_application_currency()->symbol }}</span>
                            <input type="text" name="price"
                                class="next-input input-mask-number next-input--invisible input-sync-item base-price-rule-item"
                                data-thousands-separator="{{ EcommerceHelper::getThousandSeparatorForInputMask() }}"
                                data-decimal-separator="{{ EcommerceHelper::getDecimalSeparatorForInputMask() }}"
                                data-target=".rule-price-item" value="{{ $rule ? $rule->price : 0 }}">
                        </div>
                    </div>
                </div>
                <div class="flexbox-content-no-padding pl15"></div>
            </div>
            @if ($rule)
                <div class="panel-footer overflow-hidden">
                    <div class="float-start">
                        <button class="btn btn-secondary btn-destroy btn-confirm-delete-price-item-modal-trigger"
                            data-name="{{ $rule->name }}"
                            data-id="{{ $rule->id }}">{{ trans('plugins/ecommerce::shipping.delete') }}</button>
                    </div>
                    <div class="float-end inline">
                        <button type="button" data-bs-toggle="collapse" data-bs-target="#collapse-rule-{{ $rule->id }}"
                            class="btn btn-secondary">{{ trans('plugins/ecommerce::shipping.cancel') }}</button>
                        <button class="btn btn-primary btn-save-rule">{{ trans('plugins/ecommerce::shipping.save') }}</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>
