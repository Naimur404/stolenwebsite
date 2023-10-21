<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Enums\GlobalOptionEnum;
use Botble\Ecommerce\Models\GlobalOption;
use Botble\Ecommerce\Models\GlobalOptionValue;
use Botble\Ecommerce\Repositories\Interfaces\GlobalOptionInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class GlobalOptionRepository extends RepositoriesAbstract implements GlobalOptionInterface
{
    public function createOrUpdate($data, array $condition = []): Model|bool
    {
        $optionValues = [];
        if (is_array($data)) {
            if (empty($condition)) {
                $item = new $this->model();
            } else {
                $item = $this->getFirstBy($condition);
            }

            if (empty($item)) {
                $item = new $this->model();
            }

            $optionData = [
                'name' => $data['name'],
                'option_type' => $data['option_type'],
                'required' => $data['required'],
            ];

            $optionValues = $this->formatOptionValue($data);
            $item = $item->fill($optionData);
        } elseif ($data instanceof Model) {
            $item = $data;
        } else {
            return false;
        }

        $this->resetModel();

        if ($item->save()) {
            $item->values()->whereNotIn('id', collect($optionValues)->pluck('id')->all())->delete();
            /**
             * @var GlobalOption $item
             */
            $item->values()->saveMany($optionValues);

            return $item;
        }

        return false;
    }

    protected function formatOptionValue(array $data): array
    {
        $type = explode('\\', $data['option_type']);
        $type = end($type);
        $values = [];
        // TODO change const to Enum class
        $textTypeArr = ['Field'];

        if (in_array($type, $textTypeArr)) {
            $globalOptionValue = new GlobalOptionValue();
            $item['affect_price'] = $data['affect_price'] ?? 0;
            $item['affect_type'] = $data['affect_type'] ?? GlobalOptionEnum::TYPE_PERCENT;
            $item['option_value'] = 'n/a';
            $globalOptionValue->fill($item);
            $values[] = $globalOptionValue;
        } else {
            /**
             * Other type save many option value to db
             */
            foreach (Arr::get($data, 'options', []) as $item) {
                $globalOptionValue = null;
                if (! empty($item['id'])) {
                    $globalOptionValue = GlobalOptionValue::query()->find($item['id']);
                }

                if (! $globalOptionValue) {
                    $globalOptionValue = new GlobalOptionValue();
                }

                $item['affect_price'] = ! empty($item['affect_price']) ? $item['affect_price'] : 0;
                $globalOptionValue->fill($item);
                $values[] = $globalOptionValue;
            }
        }

        return $values;
    }
}
