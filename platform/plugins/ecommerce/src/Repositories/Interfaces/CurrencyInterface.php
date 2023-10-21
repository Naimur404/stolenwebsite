<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface CurrencyInterface extends RepositoryInterface
{
    public function getAllCurrencies(): Collection;
}
