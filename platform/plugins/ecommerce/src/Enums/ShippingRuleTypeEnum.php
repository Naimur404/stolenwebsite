<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Shipping;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

/**
 * @method static ShippingRuleTypeEnum BASED_ON_PRICE()
 * @method static ShippingRuleTypeEnum BASED_ON_WEIGHT()
 * @method static ShippingRuleTypeEnum BASED_ON_ZIPCODE()
 * @method static ShippingRuleTypeEnum BASED_ON_LOCATION()
 */
class ShippingRuleTypeEnum extends Enum
{
    public const BASED_ON_PRICE = 'based_on_price';
    public const BASED_ON_WEIGHT = 'based_on_weight';
    public const BASED_ON_ZIPCODE = 'based_on_zipcode';
    public const BASED_ON_LOCATION = 'based_on_location';

    public static $langPath = 'plugins/ecommerce::shipping.rule.types';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::BASED_ON_PRICE => Html::tag('span', self::BASED_ON_PRICE()->label(), ['class' => 'text-primary'])
                ->toHtml(),
            self::BASED_ON_WEIGHT => Html::tag('span', self::BASED_ON_WEIGHT()->label(), ['class' => 'text-info'])
                ->toHtml(),
            self::BASED_ON_ZIPCODE => Html::tag('span', self::BASED_ON_ZIPCODE()->label(), ['class' => 'text-dark'])
                ->toHtml(),
            self::BASED_ON_LOCATION => Html::tag('span', self::BASED_ON_LOCATION()->label(), ['class' => 'text-success'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }

    public static function getLabel(string|null $value): string|null
    {
        $key = sprintf(
            '%s.%s',
            static::$langPath,
            $value
        );

        $replace = [];

        if ($value == self::BASED_ON_WEIGHT) {
            $replace['unit'] = self::BASED_ON_WEIGHT()->toUnit();
        }

        $label = Lang::has($key) ? trans($key, $replace) : $value;

        if ($value == self::BASED_ON_ZIPCODE && ! EcommerceHelper::isZipCodeEnabled()) {
            $label .= ' (' . trans('plugins/ecommerce::shipping.rule.types.unavailable') . ')';
        }

        if ($value == self::BASED_ON_LOCATION && ! EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            $label .= ' (' . trans('plugins/ecommerce::shipping.rule.types.unavailable') . ')';
        }

        return apply_filters(BASE_FILTER_ENUM_LABEL, $label, get_called_class());
    }

    public function label(): string|null
    {
        return self::getLabel($this->getValue());
    }

    public function toUnit(): string
    {
        return match ($this->value) {
            self::BASED_ON_PRICE => get_application_currency()->symbol,
            self::BASED_ON_WEIGHT => ecommerce_weight_unit(),
            default => '',
        };
    }

    public function toUnitText(mixed $value): mixed
    {
        return match ($this->value) {
            self::BASED_ON_PRICE => format_price($value),
            self::BASED_ON_WEIGHT => number_format($value) . ' ' . $this->toUnit(),
            default => $value,
        };
    }

    public function showFromToInputs(): bool
    {
        return match ($this->value) {
            self::BASED_ON_PRICE, self::BASED_ON_WEIGHT => true,
            default => false,
        };
    }

    public static function toSelectAttributes(): array
    {
        $result = [];

        foreach (static::toArray() as $key => $value) {
            $result[$value] = [
                'data-unit' => static::$key()->toUnit(),
                'data-text' => static::$key()->label(),
                'data-show-from-to' => (string) static::$key()->showFromToInputs(),
            ];
        }

        return $result;
    }

    public function allowRuleItems(): bool
    {
        return in_array($this->value, static::keysAllowRuleItems());
    }

    public static function keysAllowRuleItems(): array
    {
        return [
            self::BASED_ON_ZIPCODE,
            self::BASED_ON_LOCATION,
        ];
    }

    public static function availableLabels(?Shipping $shipping = null): array
    {
        $labels = parent::labels();
        if ($shipping && ! $shipping->country) {
            Arr::forget($labels, self::BASED_ON_ZIPCODE);
            Arr::forget($labels, self::BASED_ON_LOCATION);
        }

        if (! EcommerceHelper::isZipCodeEnabled()) {
            Arr::forget($labels, self::BASED_ON_ZIPCODE);
        }

        if (! EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Arr::forget($labels, self::BASED_ON_LOCATION);
        }

        return $labels;
    }
}
