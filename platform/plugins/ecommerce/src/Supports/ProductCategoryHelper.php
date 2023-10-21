<?php

namespace Botble\Ecommerce\Supports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;

class ProductCategoryHelper
{
    protected EloquentCollection $allCategories;

    protected EloquentCollection $treeCategories;

    public function getAllProductCategories(array $params = [], bool $onlyParent = false): EloquentCollection
    {
        if (! isset($this->allCategories)) {
            $this->allCategories = app(ProductCategoryInterface::class)->getProductCategories(
                Arr::get($params, 'condition', []),
                Arr::get($params, 'with', []),
                Arr::get($params, 'withCount', []),
                $onlyParent,
                Arr::get($params, 'select', ['id', 'name', 'status']),
            );
        }

        return $this->allCategories;
    }

    /**
     * @deprecated
     */
    public function getAllProductCategoriesSortByChildren(): EloquentCollection
    {
        return $this->getTreeCategories();
    }

    /**
     * @deprecated
     */
    public function getAllProductCategoriesWithChildren(): array
    {
        return $this->getTreeCategories()->toArray();
    }

    /**
     * @deprecated
     */
    public function getProductCategoriesWithIndent(): EloquentCollection
    {
        return $this->getActiveTreeCategories();
    }

    public function getActiveTreeCategories(): EloquentCollection
    {
        return $this->getTreeCategories(true);
    }

    public function getTreeCategories(bool $activeOnly = false): EloquentCollection
    {
        if (! isset($this->treeCategories)) {
            $this->treeCategories = $this->getAllProductCategories(
                [
                    'condition' => ['status' => BaseStatusEnum::PUBLISHED],
                    'with' => [$activeOnly ? 'activeChildren' : 'children'],
                ],
                true
            );
        }

        return $this->treeCategories;
    }

    public function getTreeCategoriesOptions(array $categories, array $options = [], string $indent = null): array
    {
        foreach ($categories as $category) {
            $options[$category['id']] = $indent . $category['name'];

            if (! empty($category['active_children'])) {
                $options = $this->getTreeCategoriesOptions($category['active_children'], $options, $indent . '&nbsp;&nbsp;');
            }
        }

        return $options;
    }
}
