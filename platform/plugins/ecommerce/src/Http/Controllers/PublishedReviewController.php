<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Review;

class PublishedReviewController extends BaseController
{
    public function store(string|int $id, BaseHttpResponse $response): BaseHttpResponse
    {
        $review = Review::query()
            ->where('status', BaseStatusEnum::DRAFT)
            ->findOrFail($id);

        $review->update(['status' => BaseStatusEnum::PUBLISHED]);

        return $response->setMessage(trans('plugins/ecommerce::review.published_success'));
    }

    public function destroy(string|int $id, BaseHttpResponse $response): BaseHttpResponse
    {
        $review = Review::query()
            ->wherePublished()
            ->findOrFail($id);

        $review->update(['status' => BaseStatusEnum::DRAFT]);

        return $response->setMessage(trans('plugins/ecommerce::review.unpublished_success'));
    }
}
