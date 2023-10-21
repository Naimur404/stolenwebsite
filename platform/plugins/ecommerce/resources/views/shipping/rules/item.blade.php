<div class="row my-2 box-table-shipping input-shipping-sync-wrapper box-table-shipping-item-{{ $rule ? $rule->id : 0 }}">
    <div class="col-12">
        <div class="accordion" id="accordion-rule-{{ $rule->id }}">
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-rule-{{ $rule->id }}">
                    <button class="accordion-button collapsed px-3 py-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse-rule-{{ $rule->id }}" aria-expanded="false" aria-controls="collapse-rule-{{ $rule->id }}">
                        <div class="row w-100">
                            <div class="col">
                                <span class="fw-bold label-rule-item-name">{{ $rule->name }}</span>
                                <div class="small mt-1">
                                    @if ($rule->type->allowRuleItems())
                                        <span>{{ $rule->type->label() }}</span>
                                    @else
                                        <span class="rule-to-value-missing @if ($rule->to) hidden @endif">{{ trans('plugins/ecommerce::shipping.greater_than') }}</span>
                                        <span>
                                            <span class="from-value-label">{{ $rule->type->toUnitText($rule->from) }}</span>
                                        </span>
                                        <span class="rule-to-value-wrap @if (!$rule->to) hidden @endif">
                                            <span class="m-1">-</span>
                                            <span>
                                                <span class="to-value-label">{{ $rule->type->toUnitText($rule->to) }}</span>
                                            </span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <label class="py-1 px-2">
                                    <span>
                                        <span class="rule-price-item">{{ format_price($rule->price ?? 0) }}</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse-rule-{{ $rule->id }}" class="accordion-collapse collapse" aria-labelledby="heading-rule-{{ $rule->id }}"
                    data-bs-parent="#accordion-rule-{{ $rule->id }}">
                    <div class="accordion-body shipping-detail-information">
                        @include('plugins/ecommerce::shipping.rules.form')

                        @if ($rule && $rule->type->allowRuleItems() &&
                            Auth::user()->hasPermission('ecommerce.shipping-rule-items.index'))
                            @include('plugins/ecommerce::shipping.items.index', ['total' => $rule->items_count])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
