<?php

namespace Botble\Ecommerce\Exports;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Location\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TemplateShippingRuleItemExport implements
    FromCollection,
    WithHeadings,
    WithEvents,
    WithStrictNullComparison,
    WithColumnWidths,
    ShouldAutoSize
{
    use Exportable;

    protected Collection $results;

    protected string $exportType;

    protected int $totalRow;

    protected bool $isLoadFromLocation;

    public function __construct(string $exportType = Excel::XLSX)
    {
        $this->exportType = $exportType;

        $this->isLoadFromLocation = EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation();
        $countryName = 'United States of America';
        $countryCode = 'US';
        $shippingRuleName = 'Based on zipcode';

        $states = collect([
            'Alabama' => [
                'Dothan' => [
                    '36301',
                    '36305',
                    '36302',
                    '36345',
                ],
                'Hoover' => [
                    '35022',
                    '35080',
                    '35242',
                    '35244',
                ],
                'Mobile' => [
                    '36525',
                    '36602',
                    '36606',
                    '36610',
                ],
            ],
            'Alaska' => [
                'Anchorage' => [
                    '99501',
                    '99505',
                    '99509',
                    '99514',
                ],
            ],
        ]);

        $results = [];

        $shippingRule = ShippingRule::query()->where('type', ShippingRuleTypeEnum::BASED_ON_ZIPCODE)
            ->whereHas('shipping', function (Builder $query) use ($countryCode) {
                $query->where('country', $countryCode);
            })
            ->first();

        if ($shippingRule) {
            $countries = EcommerceHelper::getAvailableCountries();
            if (Arr::has($countries, $shippingRule->shipping->country)) {
                $countryName = Arr::get($countries, $shippingRule->shipping->country);
            }
            foreach ($states as $stateName => $cities) {
                foreach ($cities as $cityName => $zipCodes) {
                    foreach ($zipCodes as $zipCode) {
                        $results[] = [
                            'shipping_rule' => $shippingRule->name,
                            'country' => $countryName,
                            'state' => $stateName,
                            'city' => $cityName,
                            'zip_code' => $zipCode,
                            'adjustment_price' => Arr::random([-2, -1.5, -1, -0.5, 0, 0.5, 1, 1.5, 2]),
                            'is_enabled' => 'Yes',
                            'type' => $shippingRule->type,
                        ];
                    }
                }
            }
        } else {
            $shippingRule = ShippingRule::query()->where('type', ShippingRuleTypeEnum::BASED_ON_ZIPCODE)->first();

            if ($shippingRule) {
                $shippingRuleName = $shippingRule->name;

                if ($shippingRule->shipping->country) {
                    if ($this->isLoadFromLocation) {
                        $country = Country::query()->where('code', $shippingRule->shipping->country)
                            ->with([
                                'states' => function ($query) {
                                    $query->limit(3);
                                },
                                'states.cities' => function ($query) {
                                    $query->limit(3);
                                },
                            ])
                            ->first();
                        if ($country) {
                            foreach ($country->states as $state) {
                                foreach ($state->cities as $city) {
                                    for ($i = 1; $i <= 3; $i++) {
                                        $results[] = [
                                            'shipping_rule' => $shippingRule->name,
                                            'country' => $country->name,
                                            'state' => $state->name,
                                            'city' => $city->name,
                                            'zip_code' => rand(10000, 99999),
                                            'adjustment_price' => Arr::random([-2, -1.5, -1, -0.5, 0, 0.5, 1, 1.5, 2]),
                                            'is_enabled' => 'Yes',
                                            'type' => $shippingRule->type,
                                        ];
                                    }
                                }
                            }
                        }
                    } else {
                        $countries = EcommerceHelper::getAvailableCountries();
                        if (Arr::has($countries, $shippingRule->shipping->country)) {
                            $countryName = Arr::get($countries, $shippingRule->shipping->country);
                        }
                    }
                }
            }
        }

        if (! $results) {
            foreach ($states as $stateName => $cities) {
                foreach ($cities as $cityName => $zipCodes) {
                    foreach ($zipCodes as $zipCode) {
                        $results[] = [
                            'shipping_rule' => $shippingRuleName,
                            'country' => $countryName,
                            'state' => $stateName,
                            'city' => $cityName,
                            'zip_code' => $zipCode,
                            'adjustment_price' => Arr::random([-2, -1.5, -1, -0.5, 0, 0.5, 1, 1.5, 2]),
                            'is_enabled' => 'Yes',
                            'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
                        ];
                    }
                }
            }
        }

        $this->results = collect($results);
        $this->totalRow = $exportType == Excel::XLSX ? 100 : ($this->results->count() + 1);
    }

    public function collection(): Collection
    {
        return $this->results;
    }

    public function headings(): array
    {
        return [
            'shipping_rule' => 'Shipping Rule', // A
            'country' => 'Country', // B
            'state' => 'State', // C
            'city' => 'City', // D
            'zip_code' => 'Zip Code', // E
            'adjustment_price' => 'Adjustment price', // F
            'is_enabled' => 'Is Enabled?', // G
            'type' => 'Type', // H
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $isEnabledColumn = 'G';
                $adjustmentPriceColumn = 'F';
                $typeColumn = 'H';

                $booleanValidation = $this->getBooleanValidation();
                $decimalValidation = $this->getDecimalValidation(-100000000000, 100000000000);
                $typeValidation = $this->getRuleItemTypesValidation();

                for ($index = 2; $index <= $this->totalRow; $index++) {
                    $event->sheet->getCell($isEnabledColumn . $index)->setDataValidation($booleanValidation);
                    $event->sheet->getCell($adjustmentPriceColumn . $index)->setDataValidation($decimalValidation);
                    $event->sheet->getCell($typeColumn . $index)->setDataValidation($typeValidation);
                }

                $delegate = $event->sheet->getDelegate();
                foreach ($this->columnFormats() as $column => $format) {
                    $delegate
                        ->getStyle($column)
                        ->getNumberFormat()
                        ->setFormatCode($format);
                }

                $delegate->getStyle('A1');
            },
        ];
    }

    protected function getDropDownListValidation(array $options = []): DataValidation
    {
        // set dropdown list for first data row
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle(trans('plugins/ecommerce::bulk-import.export.template.input_error'));
        $validation->setError(trans('plugins/ecommerce::bulk-import.export.template.value_not_in_list'));
        $validation->setPromptTitle(trans('plugins/ecommerce::bulk-import.export.template.pick_from_list'));
        $validation->setPrompt(trans('plugins/ecommerce::bulk-import.export.template.prompt_list'));
        $validation->setFormula1(sprintf('"%s"', implode(',', $options)));

        return $validation;
    }

    protected function getBooleanValidation(): DataValidation
    {
        return $this->getDropDownListValidation(['No', 'Yes']);
    }

    protected function getRuleItemTypesValidation(): DataValidation
    {
        return $this->getDropDownListValidation(ShippingRuleTypeEnum::keysAllowRuleItems());
    }

    protected function getDecimalValidation(float $min = 0, float $max = null): DataValidation
    {
        // set dropdown list for first data row
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_DECIMAL);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle(trans('plugins/ecommerce::bulk-import.export.template.input_error'));
        $validation->setError(trans('plugins/ecommerce::bulk-import.export.template.number_not_allowed'));
        $validation->setPromptTitle(trans('plugins/ecommerce::bulk-import.export.template.allowed_input'));

        if ($min != null) {
            $validation->setFormula1((string)$min);
        }
        if ($max != null) {
            $validation->setFormula2((string)$max);
        }

        if (! ($min == null && $max == null)) {
            if ($min == null) {
                $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
                $validation->setPrompt(trans('plugins/ecommerce::shipping.rule.item.bulk-import.greater_than_or_equal', compact('min')));
            } elseif ($max == null) {
                $validation->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL);
                $validation->setPrompt(trans('plugins/ecommerce::shipping.rule.item.bulk-import.less_than_or_equal', compact('max')));
            } else {
                $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
                $validation->setPrompt(trans('plugins/ecommerce::shipping.rule.item.bulk-import.between', compact('max', 'min')));
            }
        }

        return $validation;
    }

    public function columnFormats(): array
    {
        if ($this->exportType != Excel::XLSX) {
            return [];
        }

        return [
            'A2:A' . $this->totalRow => NumberFormat::FORMAT_TEXT,
            'B2:B' . $this->totalRow => NumberFormat::FORMAT_TEXT,
            'C2:C' . $this->totalRow => NumberFormat::FORMAT_TEXT,
            'D2:D' . $this->totalRow => NumberFormat::FORMAT_TEXT,
            'F2:V' . $this->totalRow => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 30,
        ];
    }

    public function rules(): array
    {
        return [
            'shipping_rule' => 'required',
            'country' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'is_enabled' => 'nullable|string (Yes or No)|default: Yes',
            'adjustment_price' => 'required|numeric|min:-100000000000|max:100000000000',
            'type' => 'nullable|enum:' . implode(',', ShippingRuleTypeEnum::keysAllowRuleItems()) . '|default:' . ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
        ];
    }
}
