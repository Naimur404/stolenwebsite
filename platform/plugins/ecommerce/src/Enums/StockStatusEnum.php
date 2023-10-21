<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static StockStatusEnum IN_STOCK()
 * @method static StockStatusEnum OUT_OF_STOCK()
 * @method static StockStatusEnum ON_BACKORDER()
 */
class StockStatusEnum extends Enum
{
    public const IN_STOCK = 'in_stock';
    public const OUT_OF_STOCK = 'out_of_stock';
    public const ON_BACKORDER = 'on_backorder';

    public static $langPath = 'plugins/ecommerce::products.stock_statuses';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::IN_STOCK => Html::tag('span', self::IN_STOCK()->label(), ['class' => 'text-success'])
                ->toHtml(),
            self::OUT_OF_STOCK => Html::tag('span', self::OUT_OF_STOCK()->label(), ['class' => 'text-danger'])
                ->toHtml(),
            self::ON_BACKORDER => Html::tag('span', self::ON_BACKORDER()->label(), ['class' => 'text-info'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
