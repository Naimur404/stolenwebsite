<?php

namespace Botble\Location\Imports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Language\Facades\Language;
use Botble\Location\Events\ImportedCityEvent;
use Botble\Location\Events\ImportedCountryEvent;
use Botble\Location\Events\ImportedStateEvent;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
use Maatwebsite\Excel\Validators\Failure;

class LocationImport implements
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

    protected FormRequest $validatorClass;

    protected string $importType = 'all';

    protected int $rowCurrent = 1; // include header

    protected array|Collection $getActiveLanguage;

    protected Collection $countries;

    protected Collection $states;

    public function __construct(protected Request $request)
    {
        $this->countries = collect();
        $this->states = collect();

        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            $this->getActiveLanguage = Language::getActiveLanguage(['lang_code', 'lang_is_default']);
        }
    }

    public function model(array $row): ?Model
    {
        $importType = $this->getImportType();

        $stateName = $this->request->input('state');
        $countryId = $this->request->input('country');

        if ($importType == 'all') {
            switch ($row['import_type']) {
                case 'city':
                    $state = $this->getStateByName($stateName, $countryId);

                    return $this->storeCity($state);
                case 'state':
                    return $this->storeState();
                case 'country':
                    return $this->storeCountry();
            }
        }

        if ($importType == 'countries' && $row['import_type'] == 'country') {
            return $this->storeCountry();
        }

        if ($importType == 'states' && $row['import_type'] == 'state') {
            return $this->storeState();
        }

        if ($importType == 'cities' && $row['import_type'] == 'city') {
            $state = $this->getStateByName($stateName, $countryId);

            return $this->storeCity($state);
        }

        return null;
    }

    public function getImportType(): string
    {
        return $this->importType;
    }

    public function setImportType(string $importType): self
    {
        $this->importType = $importType;

        return $this;
    }

    protected function getStateByName($name, $countryId): ?State
    {
        $collection = $this->states
            ->where('keyword', $name)
            ->where('country', $this->request->input('country_temp'))
            ->first();

        if ($collection) {
            return $collection['model'];
        }

        $isCreateNew = false;

        $findInColumn = is_numeric($name) ? 'name' : 'id';

        $state = State::query()->where([$findInColumn => $name, 'country_id' => $countryId])->first();

        if (! $state) {
            $state = State::query()->create(['name' => $name, 'country_id' => $countryId]);
            $isCreateNew = true;
        }

        $this->states->push(
            collect([
                'keyword' => $name,
                'is_create_new' => $isCreateNew,
                'model' => $state,
                'country' => $this->request->input('country_temp'),
            ])
        );

        return $state;
    }

    public function storeCity(State $state): ?City
    {
        $this->request->merge(['state_id' => $state->getKey()]);
        $row = $this->request->input();

        $city = City::query()->create($row);
        event(new ImportedCityEvent($row, $city));

        if ($this->getActiveLanguage) {
            foreach ($this->getActiveLanguage as $language) {
                if ($language->lang_is_default) {
                    continue;
                }

                DB::table('cities_translations')->insertOrIgnore([
                    'cities_id' => $city->id,
                    'lang_code' => $language->lang_code,
                    'name' => Arr::get($row, 'name_' . $language->lang_code) ?: Arr::get($row, 'name'),
                    'slug' => Str::slug(Arr::get($row, 'name_' . $language->lang_code) ?: Arr::get($row, 'name')),
                ]);
            }
        }

        $this->onSuccess($city);

        return $city;
    }

    public function storeState(): ?State
    {
        $row = $this->request->input();
        $collection = $this->states
            ->where('keyword', Arr::get($row, 'name'))
            ->where('country', Arr::get($row, 'country_temp'))
            ->where('is_create_new', true)
            ->first();

        if ($collection) {
            $state = $collection['model'];
        } else {
            $state = State::query()->create($row);
        }

        event(new ImportedStateEvent($row, $state));

        if ($this->getActiveLanguage) {
            foreach ($this->getActiveLanguage as $language) {
                if ($language->lang_is_default) {
                    continue;
                }

                DB::table('states_translations')->insertOrIgnore([
                    'states_id' => $state->getKey(),
                    'lang_code' => $language->lang_code,
                    'name' => Arr::get($row, 'name_' . $language->lang_code) ?: Arr::get($row, 'name'),
                    'slug' => Str::slug(Arr::get($row, 'name_' . $language->lang_code) ?: Arr::get($row, 'name')),
                    'abbreviation' => Arr::get($row, 'abbreviation_' . $language->lang_code) ?: Arr::get(
                        $row,
                        'abbreviation'
                    ),
                ]);
            }
        }

        $this->onSuccess(
            collect([
                'name' => $state->name,
                'country' => $row['country_temp'],
                'import_type' => 'state',
                'model' => $state,
            ])
        );

        $this->states->push(
            collect([
                'keyword' => $state->name,
                'is_create_new' => false,
                'model' => $state,
                'country' => $row['country_temp'],
            ])
        );

        return $state;
    }

    public function storeCountry(): ?Country
    {
        $row = $this->request->input();
        $collection = $this->countries
            ->where('keyword', $row['name'])
            ->where('is_create_new', true)
            ->first();

        if ($collection) {
            $country = $collection['model'];
        } else {
            $country = Country::query()->create($row);
        }

        event(new ImportedCountryEvent($row, $country));

        if ($this->getActiveLanguage) {
            foreach ($this->getActiveLanguage as $language) {
                if ($language->lang_is_default) {
                    continue;
                }

                DB::table('countries_translations')->insertOrIgnore([
                    'countries_id' => $country->id,
                    'lang_code' => $language->lang_code,
                    'name' => Arr::get($row, 'name_' . $language->lang_code) ?: Arr::get($row, 'name'),
                    'nationality' => Arr::get($row, 'nationality_' . $language->lang_code) ?: Arr::get(
                        $row,
                        'nationality'
                    ),
                ]);
            }
        }

        $this->countries->push([
            'keyword' => $country->name,
            'country_id' => $country->id,
        ]);

        $this->onSuccess(
            collect([
                'name' => $country->name,
                'country' => $row['country_temp'],
                'import_type' => 'country',
                'model' => $country,
            ])
        );

        return $country;
    }

    public function onStoreCityFailure(): string|null
    {
        if (method_exists($this, 'onFailure')) {
            $failures[] = new Failure(
                $this->rowCurrent,
                'state',
                [__('State name or ID ":name" does not exists', ['name' => $this->request->input('state')])],
                []
            );

            $this->onFailure(...$failures);
        }

        return null;
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
        if (in_array($row['import_type'], ['state', 'city'])) {
            $row = $this->setCountryToRow($row);
        }

        $this->request->merge($row);

        return $row;
    }

    public function mapLocalization(array $row): array
    {
        $row['status'] = strtolower(Arr::get($row, 'status'));
        if (! in_array($row['status'], BaseStatusEnum::toArray())) {
            $row['status'] = BaseStatusEnum::PUBLISHED;
        }

        $row['import_type'] = strtolower(Arr::get($row, 'import_type'));
        if (! in_array($row['import_type'], ['city', 'country'])) {
            $row['import_type'] = 'state';
        }

        $this->setValues($row, [['key' => 'order', 'type' => 'integer']]);

        $row['is_featured'] = ! ! Arr::get($row, 'is_featured');
        $row['country_temp'] = Arr::get($row, 'country');

        return $row;
    }

    protected function setCountryToRow(array $row): array
    {
        $value = trim($row['country']);
        $countryId = null;
        if ($value) {
            $countryId = $this->getCountryId($value);
        }

        $row['country_id'] = $countryId;
        $row['country'] = $countryId;

        return $row;
    }

    public function getCountryId(int|string|null $value): int|string|null
    {
        $country = $this->countries->where('keyword', $value)->first();

        if ($country) {
            $countryId = $country['country_id'];
        } else {
            $isCreateNew = false;
            if (is_numeric($value)) {
                $country = Country::query()->find($value);
            } else {
                $country = Country::query()->where(['name' => $value])->first();
            }

            if (! $country) {
                $country = Country::query()->create([
                    'name' => $value,
                    'nationality' => $this->getNationalityFromName($value),
                    'status' => BaseStatusEnum::PUBLISHED,
                ]);
                $isCreateNew = true;
            }

            $countryId = $country->id;

            $this->countries->push([
                'keyword' => $value,
                'country_id' => $countryId,
                'is_create_new' => $isCreateNew,
            ]);
        }

        return $countryId;
    }

    protected function getNationalityFromName(string $name): string
    {
        $explode = explode(' ', $name);
        if (count($explode) > 2) {
            return Str::substr($explode[0], 0, 1) . Str::substr($explode[1], 0, 1);
        }

        return Str::substr($name, 0, 2);
    }

    public function customValidationMessages(): array
    {
        return method_exists($this->getValidatorClass(), 'messages') ? $this->getValidatorClass()->messages() : [];
    }

    public function getValidatorClass(): FormRequest
    {
        return $this->validatorClass;
    }

    public function setValidatorClass(FormRequest $validatorClass): self
    {
        $this->validatorClass = $validatorClass;

        return $this;
    }

    public function rules(): array
    {
        return method_exists($this->getValidatorClass(), 'rules') ? $this->getValidatorClass()->rules() : [];
    }

    public function customValidationAttributes(): array
    {
        return method_exists($this->getValidatorClass(), 'attributes') ? $this->getValidatorClass()->attributes() : [];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
