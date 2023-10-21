<?php

namespace Botble\Location\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Language\Facades\Language;
use Botble\Location\Repositories\Interfaces\StateInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StateRepository extends RepositoriesAbstract implements StateInterface
{
    public function filters(?string $keyword, ?int $limit = 10, array $with = [], array $select = ['states.*']): Collection
    {
        $data = $this->model
            ->where('states.status', BaseStatusEnum::PUBLISHED)
            ->join('countries', 'countries.id', '=', 'states.country_id')
            ->where('countries.status', BaseStatusEnum::PUBLISHED);

        if (is_plugin_active('language') && is_plugin_active('language-advanced') && Language::getCurrentLocale() != Language::getDefaultLocale()) {
            $data = $data
                ->where(function (Builder $query) use ($keyword) {
                    return $query
                        ->whereHas('translations', function ($query) use ($keyword) {
                            $query->where('name', 'LIKE', '%' . $keyword . '%');
                        });
                });
        } else {
            $data = $data
                ->where(function (Builder $query) use ($keyword) {
                    return $query->where('states.name', 'LIKE', '%' . $keyword . '%');
                });
        }

        if ($limit) {
            $data = $data->limit($limit);
        }

        if ($with) {
            $data = $data->with($with);
        }

        return $this->applyBeforeExecuteQuery($data)->get($select);
    }
}
