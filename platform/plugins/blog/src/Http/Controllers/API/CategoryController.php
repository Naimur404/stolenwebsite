<?php

namespace Botble\Blog\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Http\Resources\CategoryResource;
use Botble\Blog\Http\Resources\ListCategoryResource;
use Botble\Blog\Models\Category;
use Botble\Blog\Repositories\Interfaces\CategoryInterface;
use Botble\Blog\Supports\FilterCategory;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List categories
     *
     * @group Blog
     */
    public function index(Request $request, BaseHttpResponse $response)
    {
        $data = Category::query()
            ->wherePublished()
            ->orderByDesc('created_at')
            ->with(['slugable'])
            ->paginate($request->integer('per_page', 10) ?: 10);

        return $response
            ->setData(ListCategoryResource::collection($data))
            ->toApiResponse();
    }

    /**
     * Filters categories
     *
     * @group Blog
     */
    public function getFilters(Request $request, BaseHttpResponse $response, CategoryInterface $categoryRepository)
    {
        $filters = FilterCategory::setFilters($request->input());
        $data = $categoryRepository->getFilters($filters);

        return $response
            ->setData(CategoryResource::collection($data))
            ->toApiResponse();
    }

    /**
     * Get category by slug
     *
     * @group Blog
     * @queryParam slug Find by slug of category.
     */
    public function findBySlug(string $slug, BaseHttpResponse $response)
    {
        $slug = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Category::class));

        if (! $slug) {
            return $response->setError()->setCode(404)->setMessage('Not found');
        }

        $category = Category::query()
            ->with('slugable')
            ->where([
                'id' => $slug->reference_id,
                'status' => BaseStatusEnum::PUBLISHED,
            ])
            ->first();

        if (! $category) {
            return $response->setError()->setCode(404)->setMessage('Not found');
        }

        return $response
            ->setData(new ListCategoryResource($category))
            ->toApiResponse();
    }
}
