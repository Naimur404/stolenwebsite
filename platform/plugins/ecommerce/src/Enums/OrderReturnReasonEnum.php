<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderReturnReasonEnum NO_LONGER_WANT()
 * @method static OrderReturnReasonEnum DAMAGED()
 * @method static OrderReturnReasonEnum DEFECTIVE()
 * @method static OrderReturnReasonEnum INCORRECT_ITEM()
 * @method static OrderReturnReasonEnum ARRIVED_LATE()
 * @method static OrderReturnReasonEnum NOT_AS_DESCRIBED()
 */
class OrderReturnReasonEnum extends Enum
{
    public const NO_LONGER_WANT = 'no_longer_want';
    public const DAMAGED = 'damaged';
    public const DEFECTIVE = 'defective';
    public const INCORRECT_ITEM = 'incorrect_item';
    public const ARRIVED_LATE = 'arrived_late';
    public const NOT_AS_DESCRIBED = 'not_as_described';
    public const OTHER = 'other';

    public static $langPath = 'plugins/ecommerce::order.order_return_reasons';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::NO_LONGER_WANT => Html::tag('span', self::NO_LONGER_WANT()->label(), ['class' => 'text-danger'])
                ->toHtml(),
            self::DEFECTIVE => Html::tag('span', self::DEFECTIVE()->label(), ['class' => 'text-danger'])
                ->toHtml(),
            self::INCORRECT_ITEM => Html::tag('span', self::INCORRECT_ITEM()->label(), ['class' => 'text-warning'])
                ->toHtml(),
            self::ARRIVED_LATE => Html::tag('span', self::ARRIVED_LATE()->label(), ['class' => 'text-warning'])
                ->toHtml(),
            self::NOT_AS_DESCRIBED => Html::tag('span', self::NOT_AS_DESCRIBED()->label(), ['class' => 'text-warning'])
                ->toHtml(),
            self::DAMAGED => Html::tag('span', self::DAMAGED()->label(), ['class' => 'text-info'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
