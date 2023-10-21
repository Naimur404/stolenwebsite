<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Review;
use Botble\Ecommerce\Tables\ReviewTable;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    public function index(ReviewTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::review.name'));

        Assets::addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        return $dataTable->renderTable();
    }

    public function show(int|string $id): View
    {
        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/admin-review.js')
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        $review = Review::query()
            ->with(['user', 'product' => function (BelongsTo $query) {
                $query
                    ->withCount('reviews')
                    ->withAvg('reviews', 'star');
            }])
            ->findOrFail($id);

        return view('plugins/ecommerce::reviews.show', compact('review'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $review = Review::query()->findOrFail($id);
            $review->delete();

            event(new DeletedContentEvent(REVIEW_MODULE_SCREEN_NAME, $request, $review));

            return $response
                ->setMessage(trans('core/base::notices.delete_success_message'))
                ->setData([
                    'next_url' => route('reviews.index'),
                ]);
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
