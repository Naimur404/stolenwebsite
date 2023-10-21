<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\TagField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\GlobalOptionEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Forms\Fields\CategoryMultiField;
use Botble\Ecommerce\Http\Requests\ProductRequest;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\GlobalOption;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Models\ProductLabel;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Tables\ProductVariationTable;

class ProductForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this->addAssets();

        $brands = Brand::query()->pluck('name', 'id')->all();
        $brands = [0 => trans('plugins/ecommerce::brands.no_brand')] + $brands;

        $productCollections = ProductCollection::query()->pluck('name', 'id')->all();

        $productLabels = ProductLabel::query()->pluck('name', 'id')->all();

        $productId = null;
        $selectedCategories = [];
        $selectedProductCollections = [];
        $selectedProductLabels = [];
        $tags = null;
        $totalProductVariations = 0;

        if ($this->getModel()) {
            $productId = $this->getModel()->id;

            $selectedCategories = $this->getModel()->categories()->pluck('category_id')->all();
            $selectedProductCollections = $this->getModel()
                ->productCollections()
                ->pluck('product_collection_id')
                ->all();
            $selectedProductLabels = $this->getModel()->productLabels()->pluck('product_label_id')->all();

            $totalProductVariations = ProductVariation::query()->where('configurable_product_id', $productId)->count();

            $tags = $this->getModel()->tags()->pluck('name')->implode(',');
        }

        $this
            ->setupModel(new Product())
            ->setValidatorClass(ProductRequest::class)
            ->withCustomFields()
            ->addCustomField('categoryMulti', CategoryMultiField::class)
            ->addCustomField('multiCheckList', MultiCheckListField::class)
            ->addCustomField('tags', TagField::class)
            ->setFormOption('files', true)
            ->add('name', 'text', [
                'label' => trans('plugins/ecommerce::products.form.name'),
                'label_attr' => ['class' => 'text-title-field required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 150,
                ],
            ])
            ->add('description', 'editor', [
                'label' => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'rows' => 2,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 1000,
                ],
            ])
            ->add('content', 'editor', [
                'label' => trans('plugins/ecommerce::products.form.content'),
                'label_attr' => ['class' => 'text-title-field'],
                'attr' => [
                    'rows' => 4,
                    'with-short-code' => true,
                ],
            ])
            ->add('images[]', 'mediaImages', [
                'label' => trans('plugins/ecommerce::products.form.image'),
                'label_attr' => ['class' => 'control-label'],
                'values' => $productId ? $this->getModel()->images : [],
            ])
            ->addMetaBoxes([
                'with_related' => [
                    'title' => null,
                    'content' => Html::tag('div', '', [
                        'class' => 'wrap-relation-product',
                        'data-target' => route('products.get-relations-boxes', $productId ?: 0),
                    ]),
                    'wrap' => false,
                    'priority' => 9999,
                ],
            ])
            ->add('product_type', 'hidden', [
                'value' => request()->input('product_type') ?: ProductTypeEnum::PHYSICAL,
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
            ->add('categories[]', 'categoryMulti', [
                'label' => trans('plugins/ecommerce::products.form.categories'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => ProductCategoryHelper::getActiveTreeCategories(),
                'value' => old('categories', $selectedCategories),
            ])
            ->add('brand_id', 'customSelect', [
                'label' => trans('plugins/ecommerce::products.form.brand'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $brands,
            ])
            ->add('image', 'mediaImage', [
                'label' => trans('plugins/ecommerce::products.form.featured_image'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->add('product_collections[]', 'multiCheckList', [
                'label' => trans('plugins/ecommerce::products.form.collections'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $productCollections,
                'value' => old('product_collections', $selectedProductCollections),
            ])
            ->add('product_labels[]', 'multiCheckList', [
                'label' => trans('plugins/ecommerce::products.form.labels'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $productLabels,
                'value' => old('product_labels', $selectedProductLabels),
            ]);

        if (EcommerceHelper::isTaxEnabled()) {
            $taxes = Tax::query()->get()->pluck('title_with_percentage', 'id')->all();

            $selectedTaxes = [];
            if ($this->getModel() && $this->getModel()->id) {
                $selectedTaxes = $this->getModel()->taxes()->pluck('tax_id')->all();
            } elseif ($defaultTaxRate = get_ecommerce_setting('default_tax_rate')) {
                $selectedTaxes = [$defaultTaxRate];
            }

            $this->add('taxes[]', 'multiCheckList', [
                'label' => trans('plugins/ecommerce::products.form.taxes'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $taxes,
                'value' => old('taxes', $selectedTaxes),
            ]);
        }

        $this
            ->add('tag', 'tags', [
                'label' => trans('plugins/ecommerce::products.form.tags'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $tags,
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::products.form.write_some_tags'),
                    'data-url' => route('product-tag.all'),
                ],
            ])
            ->setBreakFieldPoint('status');

        if (EcommerceHelper::isEnabledProductOptions()) {
            $this
                ->addMetaBoxes([
                    'product_options_box' => [
                        'title' => trans('plugins/ecommerce::product-option.name'),
                        'content' => view('plugins/ecommerce::products.partials.product-option-form', [
                            'options' => GlobalOptionEnum::options(),
                            'globalOptions' => GlobalOption::query()->pluck('name', 'id')->all(),
                            'product' => $this->getModel(),
                            'routes' => [
                                'ajax_option_info' => route('global-option.ajaxInfo'),
                            ],
                        ]),
                        'priority' => 4,
                    ],
                ]);
        }

        $productAttributeSets = ProductAttributeSet::getAllWithSelected($productId, []);

        $this
            ->addMetaBoxes([
                'attribute-sets' => [
                    'content' => '',
                    'before_wrapper' => '<div class="d-none product-attribute-sets-url" data-url="' . route('products.product-attribute-sets') . '">',
                    'after_wrapper' => '</div>',
                    'priority' => 3,
                ],
            ]);

        if (! $totalProductVariations) {
            $this
                ->removeMetaBox('variations')
                ->addMetaBoxes([
                    'general' => [
                        'title' => trans('plugins/ecommerce::products.overview'),
                        'content' => view(
                            'plugins/ecommerce::products.partials.general',
                            [
                                'product' => $productId ? $this->getModel() : null,
                                'isVariation' => false,
                                'originalProduct' => null,
                            ]
                        ),
                        'before_wrapper' => '<div id="main-manage-product-type">',
                        'priority' => 2,
                    ],
                    'attributes' => [
                        'title' => trans('plugins/ecommerce::products.attributes'),
                        'content' => view('plugins/ecommerce::products.partials.add-product-attributes', [
                            'product' => $this->getModel(),
                            'productAttributeSets' => $productAttributeSets,
                            'addAttributeToProductUrl' => $this->getModel()->id ? route('products.add-attribute-to-product', $this->getModel()->id) : null,
                        ]),
                        'after_wrapper' => '</div>',
                        'priority' => 3,
                    ],
                ]);
        } elseif ($productId) {
            $productVariationTable = app(ProductVariationTable::class)
                ->setProductId($productId)
                ->setProductAttributeSets($productAttributeSets);

            if (EcommerceHelper::isEnabledSupportDigitalProducts() && $this->getModel()->isTypeDigital()) {
                $productVariationTable->isDigitalProduct();
            }

            $this
                ->removeMetaBox('general')
                ->addMetaBoxes([
                    'variations' => [
                        'title' => trans('plugins/ecommerce::products.product_has_variations'),
                        'content' => view('plugins/ecommerce::products.partials.configurable', [
                            'product' => $this->getModel(),
                            'productAttributeSets' => $productAttributeSets,
                            'productVariationTable' => $productVariationTable,
                        ]),
                        'before_wrapper' => '<div id="main-manage-product-type">',
                        'after_wrapper' => '</div>',
                        'priority' => 3,
                        'render' => false,
                    ],
                ]);
        }

        if ($productId && is_in_admin(true)) {
            add_filter('base_action_form_actions_extra', function () {
                return view('plugins/ecommerce::forms.duplicate-action', ['product' => $this->getModel()])->render();
            });
        }
    }

    public function addAssets(): void
    {
        Assets::addStyles(['datetimepicker'])
            ->addScripts([
                'moment',
                'datetimepicker',
                'jquery-ui',
                'input-mask',
                'blockui',
            ])
            ->addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/edit-product.js',
                'vendor/core/plugins/ecommerce/js/product-option.js',
            ]);
    }
}
