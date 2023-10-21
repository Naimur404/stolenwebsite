<?php

namespace Botble\Ecommerce\Cart\Contracts;

interface Buyable
{
    public function getBuyableIdentifier($options = null): int|string;

    public function getBuyableDescription($options = null): string;

    public function getBuyablePrice($options = null): float;
}
