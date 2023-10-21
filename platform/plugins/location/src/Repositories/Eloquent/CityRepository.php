<?php

namespace Botble\Location\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Language\Facades\Language;
use Botble\Location\Repositories\Interfaces\CityInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CityRepository extends RepositoriesAbstract implements CityInterface
{
    public function filters(string|null $keyword, int|null $limit = 10, array $with = [], array $select = ['cities.*']): Collection
    {
        $data = $this->model
            ->where('cities.status', BaseStatusEnum::PUBLISHED)
            ->join('states', 'states.id', '=', 'cities.state_id')
            ->join('countries', 'countries.id', '=', 'cities.country_id')
            ->where('states.status', BaseStatusEnum::PUBLISHED)
            ->where('countries.status', BaseStatusEnum::PUBLISHED);

        if (is_plugin_active('language') && is_plugin_active('language-advanced') && Language::getCurrentLocale() != Language::getDefaultLocale()) {
            $data = $data
                ->where(function (Builder $query) use ($keyword) {
                    return $query
                        ->whereHas('translations', function ($query) use ($keyword) {
                            $query->where('name', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('state.translations', function ($query) use ($keyword) {
                            $query->where('name', 'LIKE', '%' . $keyword . '%');
                        });
                });
        } else {
            $data = $data
                ->where(function (Builder $query) use ($keyword) {
                    return $query
                        ->where('cities.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('states.name', 'LIKE', '%' . $keyword . '%');
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
