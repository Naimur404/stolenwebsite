<?php

use Botble\Base\Facades\MetaBox;
use Botble\Base\Models\MetaBox as MetaBoxModel;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Theme\Facades\Theme;

if (is_plugin_active('ecommerce')) {
    add_action(BASE_ACTION_META_BOXES, function ($context, $object) {
        if (get_class($object) == ProductCategory::class && $context == 'advanced') {
            MetaBox::addMetaBox('additional_product_category_fields', __('Addition Information'), function () {
                $icon = null;
                $iconImage = null;
                $args = func_get_args();
                if (! empty($args[0])) {
                    $icon = $args[0]->getMetaData('icon', true);
                    $iconImage = $args[0]->getMetaData('icon_image', true);
                }

                return Theme::partial('product-category-fields', compact('icon', 'iconImage'));
            }, get_class($object), $context);
        }
    }, 24, 2);

    add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($type, $request, $object) {
        if (get_class($object) == ProductCategory::class) {
            if ($request->has('icon')) {
                MetaBox::saveMetaBoxData($object, 'icon', $request->input('icon'));
            }

            if ($request->has('icon_image')) {
                MetaBox::saveMetaBoxData($object, 'icon_image', $request->input('icon_image'));
            }
        }
    }, 230, 3);

    app()->booted(function () {
        ProductCategory::resolveRelationUsing('icon', function ($model) {
            return $model->morphOne(MetaBoxModel::class, 'reference')->where('meta_key', 'icon');
        });
    });
}
