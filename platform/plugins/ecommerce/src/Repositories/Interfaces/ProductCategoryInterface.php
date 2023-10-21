<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ProductCategoryInterface extends RepositoryInterface
{
    public function getCategories(array $param);

    public function getDataSiteMap();

    public function getFeaturedCategories($limit);

    public function getAllCategories(bool $active = true);

    public function getProductCategories(
        array $conditions = [],
        array $with = [],
        array $withCount = [],
        bool $parentOnly = false,
        array $select = [],
    );
}
