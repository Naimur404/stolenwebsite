<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static ShippingCodStatusEnum PENDING()
 * @method static ShippingCodStatusEnum COMPLETED()
 */
class ShippingCodStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const COMPLETED = 'completed';

    public static $langPath = 'plugins/ecommerce::shipping.cod_statuses';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::COMPLETED => Html::tag('span', self::COMPLETED()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
