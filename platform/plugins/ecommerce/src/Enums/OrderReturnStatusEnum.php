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
 */
class OrderReturnStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const CANCELED = 'canceled';

    public static $langPath = 'plugins/ecommerce::order.return_statuses';

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
            default => parent::toHtml(),
        };
    }
}
