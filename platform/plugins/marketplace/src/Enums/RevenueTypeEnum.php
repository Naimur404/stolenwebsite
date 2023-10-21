<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static RevenueTypeEnum ADD_AMOUNT()
 * @method static RevenueTypeEnum SUBTRACT_AMOUNT()
 * @method static RevenueTypeEnum ORDER_RETURN()
 */
class RevenueTypeEnum extends Enum
{
    public const ADD_AMOUNT = 'add-amount';
    public const SUBTRACT_AMOUNT = 'subtract-amount';
    public const ORDER_RETURN = 'order-return';

    public static $langPath = 'plugins/marketplace::revenue.types';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::ADD_AMOUNT => Html::tag('span', self::ADD_AMOUNT()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::SUBTRACT_AMOUNT => Html::tag(
                'span',
                self::SUBTRACT_AMOUNT()->label(),
                ['class' => 'label-primary status-label']
            ),
            self::ORDER_RETURN => Html::tag('span', self::ORDER_RETURN()->label(), ['class' => 'label-warning status-label'])->toHtml(),
            default => parent::toHtml(),
        };
    }

    public static function adjustValues(): array
    {
        return [
            self::ADD_AMOUNT,
            self::SUBTRACT_AMOUNT,
        ];
    }

    public static function adjustLabels(): array
    {
        $result = [];

        foreach (static::adjustValues() as $value) {
            $result[$value] = static::getLabel($value);
        }

        return $result;
    }
}
