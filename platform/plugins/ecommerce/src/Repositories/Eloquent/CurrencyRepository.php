<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Repositories\Interfaces\CurrencyInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class CurrencyRepository extends RepositoriesAbstract implements CurrencyInterface
{
    public function getAllCurrencies(): Collection
    {
        $data = $this->model
            ->orderBy('order');

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
