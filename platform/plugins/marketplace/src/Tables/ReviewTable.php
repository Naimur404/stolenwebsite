<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Review;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ReviewTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Review::class);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product_id', function (Review $item) {
                if (! empty($item->product)) {
                    return Html::link(
                        $item->product->url,
                        BaseHelper::clean($item->product->name),
                        ['target' => '_blank']
                    );
                }

                return null;
            })
            ->editColumn('customer_id', function (Review $item) {
                return BaseHelper::clean($item->user->name);
            })
            ->editColumn('star', function ($item) {
                return view('plugins/ecommerce::reviews.partials.rating', ['star' => $item->star])->render();
            })
            ->editColumn('images', function (Review $item) {
                if (! is_array($item->images)) {
                    return '&mdash;';
                }

                $count = count($item->images);

                if ($count == 0) {
                    return '&mdash;';
                }

                $galleryID = 'images-group-' . $item->id;

                $html = Html::image(
                    RvMedia::getImageUrl($item->images[0], 'thumb'),
                    RvMedia::getImageUrl($item->images[0]),
                    [
                        'width' => 60,
                        'class' => 'fancybox m-1 rounded-top rounded-end rounded-bottom rounded-start border d-inline-block',
                        'href' => RvMedia::getImageUrl($item->images[0]),
                        'data-fancybox' => $galleryID,
                    ]
                );

                if (isset($item->images[1])) {
                    if ($count == 2) {
                        $html .= Html::image(
                            RvMedia::getImageUrl($item->images[1], 'thumb'),
                            RvMedia::getImageUrl($item->images[1]),
                            [
                                'width' => 60,
                                'class' => 'fancybox m-1 rounded-top rounded-end rounded-bottom rounded-start border d-inline-block',
                                'href' => RvMedia::getImageUrl($item->images[1]),
                                'data-fancybox' => $galleryID,
                            ]
                        );
                    } elseif ($count > 2) {
                        $html .= Html::tag('a', Html::image(
                            RvMedia::getImageUrl($item->images[1], 'thumb'),
                            RvMedia::getImageUrl($item->images[1]),
                            [
                                    'width' => 60,
                                    'class' => 'm-1 rounded-top rounded-end rounded-bottom rounded-start border',
                                    'src' => RvMedia::getImageUrl($item->images[1]),
                                ]
                        )->toHtml() . Html::tag('span', '+' . ($count - 2))->toHtml(), [
                            'class' => 'fancybox more-review-images',
                            'href' => RvMedia::getImageUrl($item->images[1]),
                            'data-fancybox' => $galleryID,
                        ]);
                    }
                }

                if ($count > 2) {
                    foreach ($item->images as $index => $image) {
                        if ($index > 1) {
                            $html .= Html::image(
                                RvMedia::getImageUrl($item->images[$index], 'thumb'),
                                RvMedia::getImageUrl($item->images[$index]),
                                [
                                    'width' => 60,
                                    'class' => 'fancybox d-none',
                                    'href' => RvMedia::getImageUrl($item->images[$index]),
                                    'data-fancybox' => $galleryID,
                                ]
                            );
                        }
                    }
                }

                return $html;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()
            ->select([
                'ec_reviews.id',
                'ec_reviews.star',
                'ec_reviews.comment',
                'ec_reviews.product_id',
                'ec_reviews.customer_id',
                'ec_reviews.status',
                'ec_reviews.created_at',
                'ec_reviews.images',
            ])
            ->with(['user', 'product'])
            ->join('ec_products', 'ec_products.id', 'ec_reviews.product_id')
            ->where([
                'ec_products.store_id' => auth('customer')->user()->store->id,
                'ec_reviews.status' => BaseStatusEnum::PUBLISHED,
                'ec_products.status' => BaseStatusEnum::PUBLISHED,
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            'product_id' => [
                'title' => trans('plugins/ecommerce::review.product'),
                'class' => 'text-start',
            ],
            'customer_id' => [
                'title' => trans('plugins/ecommerce::review.user'),
                'class' => 'text-start',
            ],
            'star' => [
                'title' => trans('plugins/ecommerce::review.star'),
            ],
            'comment' => [
                'title' => trans('plugins/ecommerce::review.comment'),
                'class' => 'text-start',
            ],
            'images' => [
                'title' => trans('plugins/ecommerce::review.images'),
                'width' => '150px',
                'class' => 'text-start',
                'searchable' => false,
                'orderable' => false,
            ],
            CreatedAtColumn::make(),
        ];
    }

    public function htmlDrawCallbackFunction(): ?string
    {
        return parent::htmlDrawCallbackFunction() . 'if (jQuery().fancybox) {
            $(".dataTables_wrapper .fancybox").fancybox({
                openEffect: "none",
                closeEffect: "none",
                overlayShow: true,
                overlayOpacity: 0.7,
                helpers: {
                    media: {}
                },
            });
        }';
    }
}
