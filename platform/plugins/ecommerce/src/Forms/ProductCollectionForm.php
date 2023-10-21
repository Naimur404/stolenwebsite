<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Http\Requests\ProductCollectionRequest;
use Botble\Ecommerce\Models\ProductCollection;

class ProductCollectionForm extends FormAbstract
{
    public function buildForm(): void
    {
        Assets::addStylesDirectly('vendor/core/plugins/ecommerce/css/ecommerce.css')
            ->addScripts(['blockui'])
            ->addScriptsDirectly('vendor/core/plugins/ecommerce/js/edit-product-collection.js');

        $this
            ->setupModel(new ProductCollection())
            ->setValidatorClass(ProductCollectionRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
                'help_block' => [
                    'text' => $this->getModel()->id ? trans(
                        'plugins/ecommerce::product-collections.slug_help_block',
                        ['slug' => $this->getModel()->slug]
                    ) : null,
                ],
            ])
            ->add('slug', 'text', [
                'label' => trans('core/base::forms.slug'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('plugins/ecommerce::products.form.description'),
                    'data-counter' => 400,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->add('is_featured', 'onOff', [
                'label' => trans('core/base::forms.is_featured'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('image', 'mediaImage', [
                'label' => trans('core/base::forms.image'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->setBreakFieldPoint('status');

        if ($productCollectionId = $this->getModel()->id) {
            $this
                ->addMetaBoxes([
                    'collection-products' => [
                        'title' => null,
                        'content' =>
                            Html::tag('div', '', [
                                'class' => 'wrap-collection-products',
                                'data-target' => route('product-collections.get-product-collection', $productCollectionId),
                            ]),
                        'wrap' => false,
                        'priority' => 9999,
                    ],
            ]);
        }
    }
}
