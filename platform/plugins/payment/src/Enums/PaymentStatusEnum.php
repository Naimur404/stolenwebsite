<?php

namespace Botble\Payment\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static PaymentStatusEnum PENDING()
 * @method static PaymentStatusEnum COMPLETED()
 * @method static PaymentStatusEnum REFUNDING()
 * @method static PaymentStatusEnum REFUNDED()
 * @method static PaymentStatusEnum FRAUD()
 * @method static PaymentStatusEnum FAILED()
 */
class PaymentStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const COMPLETED = 'completed';
    public const REFUNDING = 'refunding';
    public const REFUNDED = 'refunded';
    public const FRAUD = 'fraud';
    public const FAILED = 'failed';

    public static $langPath = 'plugins/payment::payment.statuses';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::COMPLETED => Html::tag('span', self::COMPLETED()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            self::REFUNDING => Html::tag('span', self::REFUNDING()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::REFUNDED => Html::tag('span', self::REFUNDED()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::FRAUD => Html::tag('span', self::FRAUD()->label(), ['class' => 'label-danger status-label'])
                ->toHtml(),
            self::FAILED => Html::tag('span', self::FAILED()->label(), ['class' => 'label-danger status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
