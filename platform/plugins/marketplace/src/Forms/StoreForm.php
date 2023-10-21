<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Location\Models\State;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Botble\Marketplace\Models\Store;

class StoreForm extends FormAbstract
{
    protected $template = 'core/base::forms.form-tabs';

    public function buildForm(): void
    {
        Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');

        $customers = Customer::query()
            ->where('is_vendor', true)
            ->pluck('name', 'id')
            ->all();

        $this
            ->setupModel(new Store())
            ->setValidatorClass(StoreRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('company', 'text', [
                'label' => trans('plugins/marketplace::store.forms.company'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.company_placeholder'),
                    'data-counter' => 255,
                ],
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('country', 'customSelect', [
                'label' => trans('plugins/marketplace::store.forms.country'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'id' => 'country_id',
                    'class' => 'form-control select-search-full',
                    'data-type' => 'country',
                ],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-4',
                ],
                'choices' => EcommerceHelper::getAvailableCountries(),
            ]);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            $states = [];
            if ($this->model) {
                $states = State::query()
                    ->where('country_id', $this->model->country)
                    ->pluck('name', 'id')
                    ->all();
            }

            $this
                ->add('state', 'customSelect', [
                    'label' => trans('plugins/location::city.state'),
                    'label_attr' => ['class' => 'control-label'],
                    'wrapper' => [
                        'class' => 'form-group col-md-4',
                    ],
                    'attr' => [
                        'id' => 'state_id',
                        'data-url' => route('ajax.states-by-country'),
                        'class' => 'form-control select-search-full',
                        'data-type' => 'state',
                    ],
                    'choices' => ($this->model->state ?
                            [
                                $this->model->state => $this->model->state_name,
                            ]
                            :
                            [0 => trans('plugins/location::city.select_state')]) + $states,
                ])
                ->add('city', 'customSelect', [
                    'label' => trans('plugins/location::city.city'),
                    'label_attr' => [
                        'class' => 'control-label',
                    ],
                    'wrapper' => [
                        'class' => 'form-group col-md-4',
                    ],
                    'attr' => [
                        'id' => 'city_id',
                        'data-url' => route('ajax.cities-by-state'),
                        'class' => 'form-control select-search-full',
                        'data-type' => 'city',
                    ],
                    'choices' => $this->model->city ?
                        [
                            $this->model->city => $this->model->city_name,
                        ]
                        :
                        [0 => trans('plugins/location::city.select_city')],
                ]);
        } else {
            $this
                ->add('state', 'text', [
                    'label' => trans('plugins/marketplace::store.forms.state'),
                    'label_attr' => ['class' => 'control-label'],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-4',
                    ],
                    'attr' => [
                        'placeholder' => trans('plugins/marketplace::store.forms.state_placeholder'),
                        'data-counter' => 120,
                    ],
                ])
                ->add('city', 'text', [
                    'label' => trans('plugins/marketplace::store.forms.city'),
                    'label_attr' => ['class' => 'control-label'],
                    'wrapper' => [
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-4',
                    ],
                    'attr' => [
                        'placeholder' => trans('plugins/marketplace::store.forms.city_placeholder'),
                        'data-counter' => 120,
                    ],
                ]);
        }
        $this
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('address', 'text', [
                'label' => trans('plugins/marketplace::store.forms.address'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.address_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('zip_code', 'text', [
                'label' => trans('plugins/marketplace::store.forms.zip_code'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-4',
                ],
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.zip_code_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('email', 'email', [
                'label' => trans('plugins/marketplace::store.forms.email'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-4',
                ],
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.email_placeholder'),
                    'data-counter' => 60,
                ],
            ])
            ->add('phone', 'text', [
                'label' => trans('plugins/marketplace::store.forms.phone'),
                'label_attr' => ['class' => 'control-label'],
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-4',
                ],
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.phone_placeholder'),
                    'data-counter' => 15,
                ],
            ])
            ->add('rowClose2', 'html', [
                'html' => '</div>',
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 400,
                ],
            ])
            ->add('content', 'editor', [
                'label' => trans('core/base::forms.content'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'with-short-code' => false,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'choices' => BaseStatusEnum::labels(),
                'help_block' => [
                    'text' => trans('plugins/marketplace::marketplace.helpers.store_status', [
                        'customer' => CustomerStatusEnum::LOCKED()->label(),
                        'status' => BaseStatusEnum::PUBLISHED()->label(),
                    ]),
                ],
            ])
            ->add('customer_id', 'customSelect', [
                'label' => trans('plugins/marketplace::store.forms.store_owner'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'choices' => [0 => trans('plugins/marketplace::store.forms.select_store_owner')] + $customers,
            ])
            ->add('logo', 'mediaImage', [
                'label' => trans('plugins/marketplace::store.forms.logo'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->setBreakFieldPoint('status');
    }
}
