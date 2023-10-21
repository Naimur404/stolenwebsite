<?php

namespace Botble\Ads\Forms;

use Botble\Ads\Facades\AdsManager;
use Botble\Ads\Http\Requests\AdsRequest;
use Botble\Ads\Models\Ads;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FormAbstract;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdsForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Ads())
            ->setValidatorClass(AdsRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('key', 'text', [
                'label' => trans('plugins/ads::ads.key'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/ads::ads.key'),
                    'data-counter' => 255,
                ],
                'default_value' => $this->generateAdsKey(),
            ])
            ->add('url', 'text', [
                'label' => trans('plugins/ads::ads.url'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/ads::ads.url'),
                    'data-counter' => 255,
                ],
            ])
            ->add('order', 'number', [
                'label' => trans('core/base::forms.order'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.order_by_placeholder'),
                ],
                'default_value' => 0,
            ])
            ->add('open_in_new_tab', 'onOff', [
                'label' => trans('plugins/ads::ads.open_in_new_tab'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => true,
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->add('location', 'customSelect', [
                'label' => trans('plugins/ads::ads.location'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => AdsManager::getLocations(),
            ])
            ->add('expired_at', 'datePicker', [
                'label' => trans('plugins/ads::ads.expired_at'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => BaseHelper::formatDate(Carbon::now()->addMonth()),
            ])
            ->add('image', 'mediaImage', [
                'label' => trans('core/base::forms.image'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->setBreakFieldPoint('status');
    }

    protected function generateAdsKey(): string
    {
        do {
            $key = strtoupper(Str::random(12));
        } while (Ads::query()->where('key', $key)->exists());

        return $key;
    }
}
