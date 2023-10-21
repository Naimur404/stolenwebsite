<?php

namespace Botble\Ads\Repositories\Eloquent;

use Botble\Ads\Repositories\Interfaces\AdsInterface;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class AdsRepository extends RepositoriesAbstract implements AdsInterface
{
    public function getAll(): Collection
    {
        // @phpstan-ignore-next-line
        $data = $this->model
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->notExpired()
            ->with(['metadata']);

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
