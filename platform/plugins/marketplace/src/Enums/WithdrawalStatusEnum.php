<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static WithdrawalStatusEnum PENDING()
 * @method static WithdrawalStatusEnum PROCESSING()
 * @method static WithdrawalStatusEnum COMPLETED()
 * @method static WithdrawalStatusEnum CANCELED()
 * @method static WithdrawalStatusEnum REFUSED()
 */
class WithdrawalStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const CANCELED = 'canceled';
    public const REFUSED = 'refused';

    public static $langPath = 'plugins/marketplace::withdrawal.statuses';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::PROCESSING => Html::tag(
                'span',
                self::PROCESSING()->label(),
                ['class' => 'label-primary status-label']
            )
                ->toHtml(),
            self::COMPLETED => Html::tag('span', self::COMPLETED()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            self::CANCELED => Html::tag('span', self::CANCELED()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::REFUSED => Html::tag('span', self::REFUSED()->label(), ['class' => 'label-danger status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
