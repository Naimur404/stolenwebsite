<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum PENDING()
 * @method static OrderStatusEnum PROCESSING()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCELED()
 * @method static OrderStatusEnum PARTIAL_RETURNED()
 * @method static OrderStatusEnum RETURNED()
 */
class OrderStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const CANCELED = 'canceled';
    public const PARTIAL_RETURNED = 'partial_returned';
    public const RETURNED = 'returned';

    public static $langPath = 'plugins/ecommerce::order.statuses';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::PROCESSING => Html::tag('span', self::PROCESSING()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::COMPLETED => Html::tag('span', self::COMPLETED()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            self::CANCELED => Html::tag('span', self::CANCELED()->label(), ['class' => 'label-danger status-label'])
                ->toHtml(),
            self::PARTIAL_RETURNED => Html::tag(
                'span',
                self::PARTIAL_RETURNED()->label(),
                ['class' => 'label-danger status-label']
            )
                ->toHtml(),
            self::RETURNED => Html::tag('span', self::RETURNED()->label(), ['class' => 'label-danger status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
