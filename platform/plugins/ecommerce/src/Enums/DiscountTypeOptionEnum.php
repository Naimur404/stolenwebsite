<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static DiscountTypeOptionEnum AMOUNT()
 * @method static DiscountTypeOptionEnum PERCENTAGE()
 * @method static DiscountTypeOptionEnum SHIPPING()
 * @method static DiscountTypeOptionEnum SAME_PRICE()
 */
class DiscountTypeOptionEnum extends Enum
{
    public const AMOUNT = 'amount';
    public const PERCENTAGE = 'percentage';
    public const SHIPPING = 'shipping';
    public const SAME_PRICE = 'same-price';

    public static $langPath = 'plugins/ecommerce::discount.enums.type-options';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::AMOUNT => Html::tag('span', self::AMOUNT()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::PERCENTAGE => Html::tag('span', self::PERCENTAGE()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::SHIPPING => Html::tag('span', self::SHIPPING()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::SAME_PRICE => Html::tag('span', self::SAME_PRICE()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
