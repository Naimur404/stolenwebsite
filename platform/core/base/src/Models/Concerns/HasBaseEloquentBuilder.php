<?php

namespace Botble\Base\Models\Concerns;

use Botble\Base\Models\BaseQueryBuilder;

trait HasBaseEloquentBuilder
{
    public function newEloquentBuilder($query): BaseQueryBuilder
    {
        return new BaseQueryBuilder($query);
    }
}
