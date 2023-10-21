<?php

namespace Botble\Faq\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\Faq\Http\Requests\FaqRequest;
use Botble\Faq\Models\Faq;
use Botble\Faq\Models\FaqCategory;

class FaqForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Faq())
            ->setValidatorClass(FaqRequest::class)
            ->withCustomFields()
            ->add('category_id', 'customSelect', [
                'label' => trans('plugins/faq::faq.category'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => ['' => trans('plugins/faq::faq.select_category')] + FaqCategory::query()->pluck('name', 'id')->all(),
            ])
            ->add('question', 'text', [
                'label' => trans('plugins/faq::faq.question'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('answer', 'editor', [
                'label' => trans('plugins/faq::faq.answer'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
