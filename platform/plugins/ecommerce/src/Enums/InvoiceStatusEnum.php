<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static InvoiceStatusEnum PENDING()
 * @method static InvoiceStatusEnum PROCESSING()
 * @method static InvoiceStatusEnum COMPLETED()
 * @method static InvoiceStatusEnum CANCELED()
 * @method static InvoiceStatusEnum RETURNED()
 */
class InvoiceStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const CANCELED = 'canceled';

    public static $langPath = 'plugins/ecommerce::invoice.statuses';

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
