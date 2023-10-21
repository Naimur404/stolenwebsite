<?php

namespace Botble\Table\Abstracts\Concerns;

use Botble\Base\Facades\BaseHelper;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

trait HasFilters
{
    protected string $filterTemplate = 'core/table::filter';

    /**
     * @deprecated since v6.8.0, using `hasFilters` instead.
     */
    public function isHasFilter(): bool
    {
        return $this->hasFilters();
    }

    public function hasFilters(): bool
    {
        return ! empty($this->getFilters());
    }

    public function isFiltering(): bool
    {
        return $this->request()->has('filter_table_id') &&
            $this->request()->input('filter_table_id') === $this->getOption('id');
    }

    public function getFilterColumns(): array
    {
        $columns = $this->getFilters();
        $columnKeys = array_keys($columns);

        return Arr::where((array)$this->request->input('filter_columns', []), function ($item) use ($columnKeys) {
            return in_array($item, $columnKeys);
        });
    }

    public function applyFilterCondition(
        EloquentBuilder|QueryBuilder|EloquentRelation $query,
        string $key,
        string $operator,
        string|null $value
    ) {
        if (strpos($key, '.') !== -1) {
            $key = Arr::last(explode('.', $key));
        }

        $column = $this->getModel()->getTable() . '.' . $key;

        $key = preg_replace('/[^A-Za-z0-9_]/', '', str_replace(' ', '', $key));

        switch ($key) {
            case 'created_at':
            case 'updated_at':
                if (! $value) {
                    break;
                }

                $validator = Validator::make([$key => $value], [$key => 'date']);

                if (! $validator->fails()) {
                    $value = BaseHelper::formatDate($value);
                    $query = $query->whereDate($column, $operator, $value);
                }

                break;

            default:
                if (! $value) {
                    break;
                }

                if ($operator === 'like') {
                    $query = $query->where($column, $operator, '%' . $value . '%');

                    break;
                }

                if ($operator !== '=') {
                    $value = (float)$value;
                }

                $query = $query->where($column, $operator, $value);
        }

        return $query;
    }

    public function renderFilter(): string
    {
        $tableId = $this->getOption('id');
        $class = get_class($this);
        $columns = $this->getFilters();

        $request = $this->request();
        $requestFilters = [
            '-1' => [
                'column' => '',
                'operator' => '=',
                'value' => '',
            ],
        ];

        $filterColumns = $this->getFilterColumns();

        if ($filterColumns) {
            $requestFilters = [];
            foreach ($filterColumns as $key => $item) {
                $operator = $request->input('filter_operators.' . $key);

                $value = $request->input('filter_values.' . $key);

                if (is_array($operator) || is_array($value) || is_array($item)) {
                    continue;
                }

                $requestFilters[] = [
                    'column' => $item,
                    'operator' => $operator,
                    'value' => $value,
                ];
            }
        }

        return view($this->filterTemplate, compact('columns', 'class', 'tableId', 'requestFilters'))->render();
    }

    public function getFilters(): array
    {
        return [];
    }
}
