<?php

namespace Botble\Marketplace\Repositories\Eloquent;

use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Repositories\Interfaces\StoreInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class StoreRepository extends RepositoriesAbstract implements StoreInterface
{
    public function handleCommissionEachCategory(array $data): array
    {
        return Store::handleCommissionEachCategory($data);
    }

    public function getCommissionEachCategory(): array
    {
        return Store::getCommissionEachCategory();
    }
}
