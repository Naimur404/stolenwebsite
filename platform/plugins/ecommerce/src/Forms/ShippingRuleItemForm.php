<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\ShippingRuleItemRequest;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;
use Illuminate\Database\Eloquent\Builder;

class ShippingRuleItemForm extends FormAbstract
{
    public function buildForm(): void
    {
        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        Assets::addScriptsDirectly(['vendor/core/plugins/ecommerce/js/shipping.js'])
            ->addScripts(['input-mask']);

        $rules = ShippingRule::query()
            ->whereIn('type', ShippingRuleTypeEnum::keysAllowRuleItems())
            ->whereHas('shipping', function (Builder $query) {
                $query->whereNotNull('country');
            })
            ->get();

        $optionAttrs = [
            0 => [
                'data-country' => '',
            ],
        ];

        $country = $this->getModel() ? $this->getModel()->country : '';
        $shippingRuleId = 0;
        if (request()->ajax()) {
            if (! $this->getModel() && request()->input('shipping_rule_id')) {
                $shippingRuleId = request()->input('shipping_rule_id');
            } elseif ($this->getModel()) {
                $shippingRuleId = $this->getModel()->shippingRule->id;
            }
        }

        $country = old('country', $country);

        $choices = [];
        foreach ($rules as $rule) {
            $choices[$rule->id] = $rule->name . ' - ' . format_price($rule->price) . ' / ' . $rule->shipping->country_name;
            $optionAttrs[$rule->id] = ['data-country' => $rule->shipping->country];
            if ($shippingRuleId && $shippingRuleId == $rule->id) {
                $country = $rule->shipping->country;
            }
        }

        $choices = [0 => trans('plugins/ecommerce::shipping.rule.item.forms.no_shipping_rule')] + $choices;

        $isRequiredState = false;
        if ($shippingRuleId) {
            $rule = $rules->firstWhere('id', $shippingRuleId);
            if ($rule) {
                $isRequiredState = $rule->type->getValue() == ShippingRuleTypeEnum::BASED_ON_LOCATION;
            }
        }

        $this
            ->setupModel(new ShippingRuleItem())
            ->setValidatorClass(ShippingRuleItemRequest::class)
            ->withCustomFields()
            ->add('shipping_rule_id', 'customSelect', [
                'label' => trans('plugins/ecommerce::shipping.rule.item.forms.shipping_rule'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control shipping-rule-id',
                ],
                'optionAttrs' => $optionAttrs,
                'choices' => $choices,
                'default_value' => $shippingRuleId,
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ($shippingRuleId ? ' d-none' : ''),
                ],
            ]);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            $states = ['' => trans('plugins/location::city.select_state')];
            if ($country) {
                $states += EcommerceHelper::getAvailableStatesByCountry($country);
            }

            $cities = ['' => trans('plugins/location::city.select_city')];
            if ($state = old('state', $this->getModel()->state)) {
                $cities += EcommerceHelper::getAvailableCitiesByState($state);
            }

            $this
                ->add('country', 'customSelect', [
                    'attr' => [
                        'data-type' => 'country',
                    ],
                    'choices' => EcommerceHelper::getAvailableCountries(),
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' d-none',
                    ],
                    'selected' => $country,
                ])
                ->add('state', 'customSelect', [
                    'label' => trans('plugins/ecommerce::shipping.rule.item.forms.state'),
                    'label_attr' => ['class' => 'control-label ' . ($isRequiredState ? 'required' : '')],
                    'attr' => [
                        'class' => 'form-control select-search-full',
                        'data-type' => 'state',
                        'data-url' => route('ajax.states-by-country'),
                    ],
                    'choices' => $states,
                ])
                ->add('city', 'customSelect', [
                    'label' => trans('plugins/ecommerce::shipping.rule.item.forms.city'),
                    'label_attr' => ['class' => 'control-label'],
                    'attr' => [
                        'class' => 'form-control select-search-full',
                        'data-type' => 'city',
                        'data-url' => route('ajax.cities-by-state'),
                    ],
                    'choices' => $cities,
                ]);
        } else {
            $this
                ->add('country', 'text', [
                    'label' => trans('plugins/ecommerce::shipping.rule.item.forms.country'),
                    'label_attr' => ['class' => 'control-label'],
                    'attr' => [
                        'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.country_placeholder'),
                    ],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' d-none',
                    ],
                    'value' => $country,
                ])
                ->add('state', 'text', [
                    'label' => trans('plugins/ecommerce::shipping.rule.item.forms.state'),
                    'label_attr' => ['class' => 'control-label ' . ($isRequiredState ? 'required' : '')],
                    'attr' => [
                        'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.state_placeholder'),
                    ],
                ])
                ->add('city', 'text', [
                    'label' => trans('plugins/ecommerce::shipping.rule.item.forms.city'),
                    'label_attr' => ['class' => 'control-label'],
                    'attr' => [
                        'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.city_placeholder'),
                    ],
                ]);
        }

        $this
            ->add('zip_code', 'text', [
                'label' => trans('plugins/ecommerce::shipping.rule.item.forms.zip_code'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.zip_code_placeholder'),
                ],
            ])
            ->add('adjustment_price', 'text', [
                'label' => trans('plugins/ecommerce::shipping.rule.item.forms.adjustment_price'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control input-mask-number',
                    'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.adjustment_price_placeholder'),
                    'data-placeholder' => '',
                ],
            ])
            ->add('is_enabled', 'onOff', [
                'label' => trans('plugins/ecommerce::shipping.rule.item.forms.is_enabled'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'default_value' => '1',
            ])
            ->setBreakFieldPoint('is_enabled');
    }
}
