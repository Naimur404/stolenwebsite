<?php

namespace Botble\Location\Imports;

use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;

class ValidateLocationImport extends LocationImport
{
    public function storeCountry(): ?Country
    {
        $country = collect($this->request->input());

        $collect = collect([
            'name' => $country['name'],
            'country' => $country['country_temp'],
            'import_type' => 'country',
            'model' => $country,
        ]);

        $this->onSuccess($collect);

        $this->countries->push([
            'keyword' => $country['name'],
            'country_id' => 1,
        ]);

        return null;
    }

    public function storeState(): ?State
    {
        $state = collect($this->request->input());

        $collect = collect([
            'name' => $state['name'],
            'country' => $state['country_temp'],
            'import_type' => 'state',
            'model' => $state,
        ]);

        $this->onSuccess($collect);

        return null;
    }

    public function storeCity($state): ?City
    {
        if (! $state) {
            $this->onStoreCityFailure();
        }

        return null;
    }

    public function mapLocalization(array $row): array
    {
        $row = parent::mapLocalization($row);

        if ($row['import_type'] == 'country') {
            $this->countries->push([
                'keyword' => $row['name'],
                'country_id' => 1,
            ]);
        }

        return $row;
    }

    protected function setCountryToRow(array $row): array
    {
        return $row;
    }

    protected function getStateByName($name, $countryId): ?State
    {
        return new State();
    }
}
