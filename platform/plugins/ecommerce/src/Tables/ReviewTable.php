<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Review;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReviewTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Review::class)
            ->addActions([
                Action::make('view')
                    ->route('reviews.show')
                    ->permission('reviews.index')
                    ->label(__('View'))
                    ->icon('fas fa-eye'),
                DeleteAction::make()->route('reviews.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product_id', function (Review $item) {
                if (! empty($item->product) && $item->product->url) {
                    return Html::link(
                        $item->product->url,
                        BaseHelper::clean($item->product_name),
                        ['target' => '_blank']
                    );
                }

                return null;
            })
            ->editColumn('customer_id', function (Review $item) {
                if (! $item->user->id) {
                    return null;
                }

                return Html::link(
                    route('customers.edit', $item->user->id),
                    BaseHelper::clean($item->user->name)
                )->toHtml();
            })
            ->editColumn('star', function (Review $item) {
                return view('plugins/ecommerce::reviews.partials.rating', ['star' => $item->star])->render();
            })
            ->editColumn('comment', function (Review $review) {
                return Html::link(route('reviews.show', $review), $review->comment);
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
                        $html .= Html::tag(
                            'a',
                            Html::image(
                                RvMedia::getImageUrl($item->images[1], 'thumb'),
                                RvMedia::getImageUrl($item->images[1]),
                                [
                                    'width' => 60,
                                    'class' => 'm-1 rounded-top rounded-end rounded-bottom rounded-start border',
                                    'src' => RvMedia::getImageUrl($item->images[1]),
                                ]
                            )->toHtml() . Html::tag('span', '+' . ($count - 2))->toHtml(),
                            [
                                'class' => 'fancybox more-review-images',
                                'href' => RvMedia::getImageUrl($item->images[1]),
                                'data-fancybox' => $galleryID,
                            ]
                        );
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
            })
            ->filter(function ($query) {
                $keyword = $this->request->input('search.value');
                if ($keyword) {
                    return $query->where(function ($query) use ($keyword) {
                        return $query
                            ->whereHas('product', function ($subQuery) use ($keyword) {
                                return $subQuery->where('ec_products.name', 'LIKE', '%' . $keyword . '%');
                            })
                            ->orWhereHas('user', function ($subQuery) use ($keyword) {
                                return $subQuery->where('ec_customers.name', 'LIKE', '%' . $keyword . '%');
                            })
                            ->orWhere('comment', 'LIKE', '%' . $keyword . '%');
                    });
                }

                return $query;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'star',
                'comment',
                'product_id',
                'customer_id',
                'status',
                'created_at',
                'images',
            ])
            ->with(['user', 'product']);

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
            'status' => [
                'title' => trans('plugins/ecommerce::review.status'),
            ],
            CreatedAtColumn::make(),
        ];
    }

    public function getOperationsHeading(): array
    {
        return [
            'operations' => [
                'title' => trans('core/base::tables.operations'),
                'width' => '50px',
                'class' => 'text-end',
                'orderable' => false,
                'searchable' => false,
                'exportable' => false,
                'printable' => false,
            ],
        ];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('review.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function htmlDrawCallbackFunction(): string|null
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

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->isEmpty()) {
            return view('plugins/ecommerce::reviews.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
}
