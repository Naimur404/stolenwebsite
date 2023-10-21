<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\ReviewRequest;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Review;
use Botble\Media\Facades\RvMedia;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, BaseHttpResponse $response): BaseHttpResponse
    {
        if (! EcommerceHelper::isReviewEnabled()) {
            abort(404);
        }

        $customerId = auth('customer')->id();
        $productId = $request->input('product_id');

        $check = $this->check($productId);
        if (Arr::get($check, 'error')) {
            return $response->setError()->setMessage(Arr::get($check, 'message', __('Oops! Something Went Wrong.')));
        }

        $results = [];
        if ($request->hasFile('images')) {
            $images = (array)$request->file('images', []);
            foreach ($images as $image) {
                $result = RvMedia::handleUpload($image, 0, 'reviews');
                if ($result['error']) {
                    return $response->setError()->setMessage($result['message']);
                }

                $results[] = $result;
            }
        }

        $data = $request->validated();
        $data = array_merge($data, [
            'customer_id' => $customerId,
            'images' => $results ? collect($results)->pluck('data.url')->values()->toArray() : null,
        ]);

        Review::query()->create($data);

        return $response->setMessage(__('Added review successfully!'));
    }

    public function destroy(int|string $id, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isReviewEnabled()) {
            abort(404);
        }

        $review = Review::query()->findOrFail($id);

        if (auth()->check() || (auth('customer')->check() && auth('customer')->id() == $review->customer_id)) {
            $review->delete();

            return $response->setMessage(__('Deleted review successfully!'));
        }

        abort(401);
    }

    public function getProductReview(string $key, BaseHttpResponse $response)
    {
        if (! EcommerceHelper::isReviewEnabled()) {
            abort(404);
        }

        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(Product::class));

        if (! $slug) {
            abort(404);
        }

        $condition = [
            'ec_products.id' => $slug->reference_id,
        ];

        $product = get_products(array_merge([
                'condition' => $condition,
                'take' => 1,
            ], EcommerceHelper::withReviewsParams()));

        if (! $product) {
            abort(404);
        }

        $check = $this->check($product->id);
        if (Arr::get($check, 'error')) {
            return $response
                ->setNextUrl($product->url)
                ->setError()
                ->setMessage(Arr::get($check, 'message', __('Oops! Something Went Wrong.')));
        }

        Theme::asset()
            ->add('ecommerce-review-css', 'vendor/core/plugins/ecommerce/css/review.css');
        Theme::asset()->container('footer')
            ->add('ecommerce-review-js', 'vendor/core/plugins/ecommerce/js/review.js', ['jquery']);

        SeoHelper::setTitle(__('Review product ":product"', ['product' => $product->name]))->setDescription($product->description);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Products'), route('public.products'))
            ->add($product->name, $product->url)
            ->add(__('Review'));

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, PRODUCT_MODULE_SCREEN_NAME, $product);

        return Theme::scope('ecommerce.product-review', compact('product'), 'plugins/ecommerce::themes.product-review')
            ->render();
    }

    protected function check(int|string $productId)
    {
        $customerId = auth('customer')->id();

        $exists = Review::query()
            ->where([
                'customer_id' => $customerId,
                'product_id' => $productId,
            ])
        ->count();

        if ($exists > 0) {
            return [
                'error' => true,
                'message' => __('You have reviewed this product already!'),
            ];
        }

        if (EcommerceHelper::onlyAllowCustomersPurchasedToReview()) {
            $order = Order::query()
                ->where([
                    'user_id' => $customerId,
                    'status' => OrderStatusEnum::COMPLETED,
                ])
                ->join('ec_order_product', function ($query) use ($productId) {
                    $query
                        ->on('ec_order_product.order_id', 'ec_orders.id')
                        ->leftJoin('ec_product_variations', 'ec_product_variations.product_id', 'ec_order_product.product_id')
                        ->where(function ($query) use ($productId) {
                            $query->where('ec_product_variations.configurable_product_id', $productId)
                            ->orWhere('ec_order_product.product_id', $productId);
                        });
                })
                ->count();

            if (! $order) {
                return [
                    'error' => true,
                    'message' => __('Please purchase the product for a review!'),
                ];
            }
        }

        return [
            'error' => false,
        ];
    }
}
