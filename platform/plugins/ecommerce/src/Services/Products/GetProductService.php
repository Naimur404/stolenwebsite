<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetProductService
{
    public function __construct(protected ProductInterface $productRepository)
    {
    }

    public function getProduct(
        Request $request,
        $category = null,
        $brand = null,
        array $with = [],
        array $withCount = [],
        array $conditions = []
    ): Collection|LengthAwarePaginator {
        $num = $request->integer('num');
        $shows = EcommerceHelper::getShowParams();

        if (! array_key_exists($num, $shows)) {
            $num = (int)theme_option('number_of_products_per_page', 12);
        }

        $queryVar = [
            'keyword' => BaseHelper::stringify($request->input('q')),
            'brands' => (array)$request->input('brands', []),
            'categories' => (array)$request->input('categories', []),
            'tags' => (array)$request->input('tags', []),
            'collections' => (array)$request->input('collections', []),
            'attributes' => (array)$request->input('attributes', []),
            'max_price' => $request->input('max_price'),
            'min_price' => $request->input('min_price'),
            'sort_by' => $request->input('sort-by'),
            'num' => $num,
        ];

        if ($category) {
            $queryVar['categories'] = array_merge($queryVar['categories'], [$category]);
        }

        if ($brand) {
            $queryVar['brands'] = array_merge(($queryVar['brands']), [$brand]);
        }

        $countAttributeGroups = 1;
        if (count($queryVar['attributes'])) {
            $countAttributeGroups = DB::table('ec_product_attributes')
                ->whereIn('id', $queryVar['attributes'])
                ->distinct('attribute_set_id')
                ->count('attribute_set_id');
        }

        $orderBy = [
            'ec_products.order' => 'ASC',
            'ec_products.created_at' => 'DESC',
        ];

        if (! EcommerceHelper::isReviewEnabled() && in_array($queryVar['sort_by'], ['rating_asc', 'rating_desc'])) {
            $queryVar['sort_by'] = 'date_desc';
        }

        $params = array_merge([
            'paginate' => [
                'per_page' => $queryVar['num'],
                'current_paged' => $request->integer('page', 1) ?: 1,
            ],
            'with' => array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $with),
            'withCount' => $withCount,
        ], EcommerceHelper::withReviewsParams());

        switch ($queryVar['sort_by']) {
            case 'date_asc':
                $orderBy = [
                    'ec_products.created_at' => 'ASC',
                ];

                break;
            case 'date_desc':
                $orderBy = [
                    'ec_products.created_at' => 'DESC',
                ];

                break;
            case 'price_asc':
                $orderBy = [
                    'products_with_final_price.final_price' => 'ASC',
                ];

                break;
            case 'price_desc':
                $orderBy = [
                    'products_with_final_price.final_price' => 'DESC',
                ];

                break;
            case 'name_asc':
                $orderBy = [
                    'ec_products.name' => 'ASC',
                ];

                break;
            case 'name_desc':
                $orderBy = [
                    'ec_products.name' => 'DESC',
                ];

                break;
            case 'rating_asc':
                if (EcommerceHelper::isReviewEnabled()) {
                    $orderBy = [
                        'reviews_avg' => 'ASC',
                    ];
                }

                break;
            case 'rating_desc':
                if (EcommerceHelper::isReviewEnabled()) {
                    $orderBy = [
                        'reviews_avg' => 'DESC',
                    ];
                }

                break;
        }

        if (! empty($conditions)) {
            $params['condition'] = $conditions;
        }

        $products = $this->productRepository->filterProducts([
            'keyword' => $queryVar['keyword'],
            'min_price' => $queryVar['min_price'],
            'max_price' => $queryVar['max_price'],
            'categories' => $queryVar['categories'],
            'tags' => $queryVar['tags'],
            'collections' => $queryVar['collections'],
            'brands' => $queryVar['brands'],
            'attributes' => $queryVar['attributes'],
            'count_attribute_groups' => $countAttributeGroups,
            'order_by' => $orderBy,
        ], $params);

        if ($keyword = $queryVar['keyword']) {
            $products->setCollection(BaseHelper::sortSearchResults($products->getCollection(), $keyword, 'name'));
        }

        return $products;
    }
}
