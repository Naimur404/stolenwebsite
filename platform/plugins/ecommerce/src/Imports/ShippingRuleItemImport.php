<?php

namespace Botble\Ecommerce\Imports;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Shipping;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;
use Botble\Location\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\RequiredIf;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;

class ShippingRuleItemImport implements
    ToModel,
    WithHeadingRow,
    WithMapping,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    WithChunkReading
{
    use Importable;
    use SkipsFailures;
    use SkipsErrors;
    use ImportTrait;

    protected string $importType = 'overwrite';

    protected array $availableCountries;

    protected Collection $countries;

    protected Collection $shippingRules;

    protected bool $isLoadFromLocation;

    protected int $rowCurrent = 1; // include header

    protected Request $validatorClass;

    public function __construct(protected Request $request)
    {
        $this->availableCountries = EcommerceHelper::getAvailableCountries();
        $this->isLoadFromLocation = EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation();

        $this->countries = collect();
        $this->shippingRules = collect();
    }

    public function setImportType(string $importType): self
    {
        $this->importType = $importType;

        return $this;
    }

    public function getImportType(): string
    {
        return $this->importType;
    }

    public function model(array $row)
    {
        $importType = $this->getImportType();

        if ($row['shipping_rule_id'] == 0) {
            $shippingRuleRef = $this->shippingRules
                ->where('shipping_rule', $row['shipping_rule'])
                ->where('country', $row['country'])
                ->where('type', $row['type'])
                ->first();

            if ($shippingRuleRef && $shippingRuleRef['shipping_rule_id']) {
                $row['shipping_rule_id'] = $shippingRuleRef['shipping_rule_id'];
            } else {
                $shippingRule = ShippingRule::query()
                    ->create([
                        'name' => $row['shipping_rule'],
                        'type' => $row['type'],
                        'price' => 0,
                        'shipping_id' => $row['shipping_id'],
                    ]);

                $this->shippingRules = $this->shippingRules
                    ->map(function ($value) use ($row, $shippingRule) {
                        if ($value['shipping_rule'] == $row['shipping_rule'] &&
                            $value['country'] == $row['country'] &&
                            $value['shipping_rule_id'] == 0) {
                            $value['shipping_rule_id'] = $shippingRule->id;
                        }

                        return $value;
                    });

                $row['shipping_rule_id'] = $shippingRule->id;
            }
        }

        $condition = [
            'shipping_rule_id' => $row['shipping_rule_id'],
            'country' => $row['country'],
            'state' => $row['state'],
            'city' => $row['city'],
            'zip_code' => $row['zip_code'],
        ];

        $shippingRuleItem = null;
        $isCreateOrUpdate = false;
        if ($importType == 'add_new') {
            $shippingRuleItem = ShippingRuleItem::query()->create(
                array_merge($condition, [
                    'adjustment_price' => $row['adjustment_price'],
                    'is_enabled' => $row['is_enabled'],
                ])
            );
            $isCreateOrUpdate = true;
        } else {
            $shippingRuleCount = ShippingRuleItem::query()->where($condition)->count();
            if ($shippingRuleCount) {
                if ($importType == 'overwrite') {
                    $shippingRuleItem = ShippingRuleItem::query()
                        ->where($condition)
                        ->create([
                            'adjustment_price' => $row['adjustment_price'],
                            'is_enabled' => $row['is_enabled'],
                        ]);
                    $isCreateOrUpdate = true;
                }
            } else {
                $shippingRuleItem = ShippingRuleItem::query()->create(
                    array_merge($condition, [
                        'adjustment_price' => $row['adjustment_price'],
                        'is_enabled' => $row['is_enabled'],
                    ])
                );
                $isCreateOrUpdate = true;
            }
        }

        if ($isCreateOrUpdate) {
            $this->onSuccess($shippingRuleItem);
        }

        return $shippingRuleItem;
    }

    public function getShippingRule(string $name, string|null $country, string|null $type): ShippingRule|null
    {
        return ShippingRule::query()
            ->where([
                'name' => $name,
                'type' => $type,
            ])
            ->whereHas('shipping', function ($query) use ($country) {
                $query->where('country', $country);
            })
            ->first();
    }

    public function getShipping(string|null $country): Shipping|null
    {
        return Shipping::query()->where('country', $country)->first();
    }

    /**
     * Change value before insert to model
     *
     * @param array $row
     */
    public function map($row): array
    {
        ++$this->rowCurrent;
        $row = $this->mapLocalization($row);
        $row = $this->setCountryToRow($row);
        $row = $this->setStateToRow($row);
        $row = $this->setCityToRow($row);

        return $this->setShippingRuleToRow($row);
    }

    protected function setCountryToRow(array $row): array
    {
        $row['country'] = trim(Arr::get($row, 'country', ''));
        if ($row['country']) {
            $row['country'] = array_search($row['country'], $this->availableCountries);
        }

        return $row;
    }

    protected function setStateToRow(array $row): array
    {
        if ($this->isLoadFromLocation && $row['country']) {
            $stateName = trim(Arr::get($row, 'state', ''));
            $row['state'] = '';
            $country = $this->countries->where('id', $row['country'])->first();

            if (! $country) {
                $country = Country::query()->where(['id' => $row['country']])
                    ->with(['states', 'states.cities'])
                    ->first();

                $this->countries->push([
                    'id' => $row['country'],
                    'model' => $country,
                ]);
            } else {
                $country = $country['model'];
            }

            if ($country instanceof Country && $country->id) {
                $state = $country->states->first(function ($value) use ($stateName) {
                    return $value->name == $stateName || $value->id == $stateName;
                });
                if ($state) {
                    $row['state'] = $state->id;
                }
            }
        }

        return $row;
    }

    protected function setCityToRow(array $row): array
    {
        if ($this->isLoadFromLocation && $row['country'] && $row['state']) {
            $cityName = trim(Arr::get($row, 'city', ''));

            $row['city'] = '';
            $country = $this->countries->where('id', $row['country'])->first();
            if ($country) {
                $country = $country['model'];
                if ($country instanceof Country && $country->id) {
                    $state = $country->states->where('id', $row['state'])->first();
                    if ($state) {
                        $city = $state->cities->first(function ($value) use ($cityName) {
                            return $value->name == $cityName || $value->id == $cityName;
                        });

                        if ($city) {
                            $row['city'] = $city->id;
                        }
                    }
                }
            }
        }

        return $row;
    }

    protected function setShippingRuleToRow(array $row): array
    {
        $row['shipping_rule_id'] = 0;

        if (! empty($row['shipping_rule'])) {
            $row['shipping_rule'] = trim($row['shipping_rule']);
            $country = $row['country'];

            $shippingRule = $this->shippingRules->where('shipping_rule', $row['shipping_rule'])
                ->where('country', $country)
                ->where('type', $row['type'])
                ->first();

            $shippingRuleId = 0;
            if ($shippingRule) {
                $shippingRuleId = $shippingRule['shipping_rule_id'];
            } else {
                $shippingRule = $this->getShippingRule($row['shipping_rule'], $country, $row['type']);
                if (! $shippingRule) {
                    $shipping = $this->getShipping($country);
                    if ($shipping) {
                        $row['shipping_id'] = $shipping->id;
                    } else {
                        $row['country'] = '';
                    }
                } else {
                    $shippingRuleId = $shippingRule->id;
                }

                $this->shippingRules->push([
                    'shipping_rule' => $row['shipping_rule'],
                    'country' => $country,
                    'type' => $row['type'],
                    'shipping_rule_id' => $shippingRuleId,
                ]);
            }

            $row['shipping_rule_id'] = $shippingRuleId;
        }

        return $row;
    }

    public function mapLocalization(array $row): array
    {
        $row['import_type'] = (string)Arr::get($row, 'import_type');
        if (! in_array($row['import_type'], ['overwrite', 'add_new', 'skip'])) {
            $row['import_type'] = 'overwrite';
        }

        $row['type'] = (string)Arr::get($row, 'type');
        if (! in_array($row['type'], ShippingRuleTypeEnum::keysAllowRuleItems())) {
            $row['type'] = ShippingRuleTypeEnum::BASED_ON_ZIPCODE;
        }

        $this->setValues($row, [
            ['key' => 'shipping_rule', 'type' => 'string'],
            ['key' => 'country', 'type' => 'string'],
            ['key' => 'state', 'type' => 'string'],
            ['key' => 'city', 'type' => 'string'],
            ['key' => 'zip_code', 'type' => 'string'],
            ['key' => 'adjustment_price', 'type' => 'number'],
            ['key' => 'is_enabled', 'type' => 'bool'],
        ]);

        return $row;
    }

    protected function setValues(array &$row, array $attributes = []): self
    {
        foreach ($attributes as $attribute) {
            $this->setValue(
                $row,
                Arr::get($attribute, 'key'),
                Arr::get($attribute, 'type', 'array'),
                Arr::get($attribute, 'default'),
                Arr::get($attribute, 'from')
            );
        }

        return $this;
    }

    protected function setValue(array &$row, string $key, string $type = 'array', $default = null, $from = null): self
    {
        $value = Arr::get($row, $from ?: $key, $default);

        switch ($type) {
            case 'array':
                $value = $value ? explode(',', $value) : [];

                break;
            case 'bool':
                if (Str::lower($value) == 'false' || $value == '0' || Str::lower($value) == 'no') {
                    $value = false;
                }
                $value = $value ? 1 : 0;

                break;
            case 'datetime':
                if ($value) {
                    if (in_array(gettype($value), ['integer', 'double'])) {
                        $value = $this->transformDate($value);
                    } else {
                        $value = $this->getDate($value);
                    }
                }

                break;
        }

        Arr::set($row, $key, $value);

        return $this;
    }

    public function rules(): array
    {
        $rules = method_exists($this->getValidatorClass(), 'rules') ? $this->getValidatorClass()->rules() : [];

        if ($rules) {
            if (is_array(Arr::get($rules, 'shipping_rule_id'))) {
                foreach ($rules['shipping_rule_id'] as $key => $value) {
                    if ($value instanceof Exists) {
                        Arr::forget($rules, 'shipping_rule_id.' . $key);
                    }
                }
            }

            if (is_array(Arr::get($rules, 'zip_code'))) {
                foreach ($rules['zip_code'] as $key => $value) {
                    if ($value instanceof RequiredIf) {
                        Arr::forget($rules, 'zip_code.' . $key);
                    }
                }
            }

            if (is_array(Arr::get($rules, 'city'))) {
                foreach ($rules['city'] as $key => $value) {
                    if ($value instanceof RequiredIf) {
                        Arr::forget($rules, 'zip_code.' . $key);
                    }
                }
            }
        }

        return $rules;
    }

    public function customValidationMessages(): array
    {
        return [
            'country' => trans('validation.exists'),
            'state' => trans('validation.exists'),
            'city' => trans('validation.exists'),
        ];
    }

    public function getValidatorClass(): Request
    {
        return $this->validatorClass;
    }

    public function setValidatorClass(Request $validatorClass): self
    {
        $this->validatorClass = $validatorClass;

        return $this;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
