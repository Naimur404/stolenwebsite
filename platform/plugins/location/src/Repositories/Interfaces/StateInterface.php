<?php

namespace Botble\Location\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface StateInterface extends RepositoryInterface
{
    public function filters(string|null $keyword, int|null $limit = 10, array $with = [], array $select = ['states.*']): Collection;
}
