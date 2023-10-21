<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static DiscountTypeEnum COUPON()
 * @method static DiscountTypeEnum PROMOTION()
 */
class DiscountTypeEnum extends Enum
{
    public const COUPON = 'coupon';
    public const PROMOTION = 'promotion';

    public static $langPath = 'plugins/ecommerce::discount.enums.types';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::COUPON => Html::tag('span', self::COUPON()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::PROMOTION => Html::tag('span', self::PROMOTION()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
