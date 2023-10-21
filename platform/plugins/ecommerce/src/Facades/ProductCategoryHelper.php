<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\ProductCategoryHelper as BaseProductCategoryHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getAllProductCategories(array $params = [], bool $onlyParent = false)
 * @method static \Illuminate\Database\Eloquent\Collection getActiveTreeCategories()
 * @method static \Illuminate\Database\Eloquent\Collection getTreeCategories(bool $activeOnly = false)
 * @method static array getTreeCategoriesOptions(array $categories, array $options = [], string|null $indent = null)
 *
 * @see \Botble\Ecommerce\Supports\ProductCategoryHelper
 */
class ProductCategoryHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseProductCategoryHelper::class;
    }
}
