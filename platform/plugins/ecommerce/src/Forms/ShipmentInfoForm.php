<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Http\Requests\ShipmentRequest;
use Botble\Ecommerce\Models\Shipment;

class ShipmentInfoForm extends FormAbstract
{
    protected $template = 'core/base::forms.form-content-only';

    public function buildForm(): void
    {
        $this
            ->setupModel(new Shipment())
            ->setValidatorClass(ShipmentRequest::class)
            ->withCustomFields()
            ->add('shipping_company_name', 'text', [
                'label' => trans('plugins/ecommerce::shipping.shipping_company_name'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => 'Ex: DHL, AliExpress...',
                ],
            ])
            ->add('tracking_id', 'text', [
                'label' => trans('plugins/ecommerce::shipping.tracking_id'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => 'Ex: JJD0099999999',
                ],
            ])
            ->add('tracking_link', 'text', [
                'label' => trans('plugins/ecommerce::shipping.tracking_link'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => 'Ex: https://mydhl.express.dhl/us/en/tracking.html#/track-by-reference',
                ],
            ])
            ->add('estimate_date_shipped', 'datePicker', [
                'label' => trans('plugins/ecommerce::shipping.estimate_date_shipped'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->add('note', 'textarea', [
                'label' => trans('plugins/ecommerce::shipping.note'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 3,
                    'placeholder' => trans('plugins/ecommerce::shipping.add_note'),
                ],
            ])
            ->add('submit', 'button', [
                'label' => '<i class="fa fa-check-circle me-2"></i>' . trans('core/base::forms.save'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'btn btn-success',
                    'value' => 'save',
                    'type' => 'submit',
                    'name' => 'submit',
                ],
            ]);
    }
}
