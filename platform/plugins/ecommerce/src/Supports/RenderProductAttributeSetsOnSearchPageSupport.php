<?php

namespace Botble\Ecommerce\Supports;

use Botble\Ecommerce\Facades\EcommerceHelper as EcommerceHelperFacade;
use Botble\Ecommerce\Models\ProductAttributeSet;

class RenderProductAttributeSetsOnSearchPageSupport
{
    public function render(array $params = []): string
    {
        if (! EcommerceHelperFacade::isEnabledFilterProductsByAttributes()) {
            return '';
        }

        $params = array_merge(['view' => 'plugins/ecommerce::themes.attributes.attributes-filter-renderer'], $params);

        $with = ['attributes', 'categories:id'];

        if (is_plugin_active('language') && is_plugin_active('language-advanced')) {
            $with[] = 'attributes.translations';
        }

        $attributeSets = ProductAttributeSet::query()
            ->where('is_searchable', true)
            ->wherePublished()
            ->orderBy('order')
            ->with($with)
            ->get();

        return view($params['view'], array_merge($params, compact('attributeSets')))->render();
    }
}
