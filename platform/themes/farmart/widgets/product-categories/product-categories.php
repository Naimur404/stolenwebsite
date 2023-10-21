<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Widget\AbstractWidget;
use Illuminate\Support\Collection;

class ProductCategoriesWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Product Categories'),
            'description' => __('List of product categories'),
            'categories' => [],
        ]);
    }

    protected function data(): array|Collection
    {
        $categoryIds = $this->getConfig()['categories'];

        if (empty($categoryIds) || ! is_plugin_active('ecommerce')) {
            return [
                'categories' => [],
            ];
        }

        $categories = ProductCategory::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->whereIn('id', $categoryIds)
            ->with('slugable')
            ->get();

        return [
            'categories' => $categories,
        ];
    }
}
