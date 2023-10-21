<?php

namespace Botble\Location\Exports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Language\Facades\Language;
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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TemplateLocationExport implements
    FromCollection,
    WithHeadings,
    WithEvents,
    WithStrictNullComparison,
    WithColumnWidths,
    ShouldAutoSize
{
    use Exportable;

    protected Collection $results;

    protected int $totalRow;

    public function __construct(protected string $exportType = Excel::XLSX)
    {
        $locations = [
            [
                'name' => 'United States of America',
                'slug' => '',
                'abbreviation' => '',
                'state' => '',
                'country' => '',
                'import_type' => 'country',
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 0,
                'nationality' => 'Americans',
            ],
            [
                'name' => 'Texas',
                'slug' => '',
                'abbreviation' => 'TX',
                'state' => '',
                'country' => 'United States of America',
                'import_type' => 'state',
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 0,
                'nationality' => '',
            ],
            [
                'name' => 'Washington',
                'slug' => '',
                'abbreviation' => 'WA',
                'state' => '',
                'country' => 'United States of America',
                'import_type' => 'state',
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 0,
                'nationality' => '',
            ],
            [
                'name' => 'Houston',
                'slug' => 'houston',
                'abbreviation' => '',
                'state' => 'Texas',
                'country' => 'United States of America',
                'import_type' => 'city',
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 0,
                'nationality' => '',
            ],
            [
                'name' => 'San Antonio',
                'slug' => 'san-antonio',
                'abbreviation' => '',
                'state' => 'Texas',
                'country' => 'United States of America',
                'import_type' => 'city',
                'status' => BaseStatusEnum::PUBLISHED,
                'order' => 0,
                'nationality' => '',
            ],
        ];

        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            $defaultLanguage = Language::getDefaultLanguage(['lang_code'])->lang_code;

            $supportedLocales = Language::getSupportedLocales();
            foreach ($supportedLocales as $properties) {
                if ($properties['lang_code'] != $defaultLanguage && $properties['lang_code'] == 'vi') {
                    $locations[1]['name_vi'] = 'Bang Texas';
                    $locations[2]['name_vi'] = 'Bang Washington';
                    $locations[3]['name_vi'] = 'Thành phố Houston';
                    $locations[4]['name_vi'] = 'Thành phố San Antonio';
                }
            }
        }

        $this->results = collect($locations);
        $this->totalRow = $exportType == Excel::XLSX ? 100 : ($this->results->count() + 1);
    }

    public function collection(): Collection
    {
        return $this->results;
    }

    public function headings(): array
    {
        $headings = [
            'name' => 'Name', // 1 => A
            'slug' => 'Slug', // 2 => B
            'abbreviation' => 'Abbreviation', // 3 => C
            'state' => 'State', // 4 => D
            'country' => 'Country', // 5 => E
            'import_type' => 'Import Type', // 6 => F
            'status' => 'Status', // 7 => G
            'order' => 'Order', // 8 => H
            'nationality' => 'Nationality', // 9 => I
        ];
        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            $defaultLanguage = Language::getDefaultLanguage(['lang_code'])->lang_code;

            $supportedLocales = Language::getSupportedLocales();
            foreach ($supportedLocales as $properties) {
                if ($properties['lang_code'] != $defaultLanguage && $properties['lang_code'] == 'vi') {
                    $headings['name_vi'] = 'Name VI';
                }
            }
        }

        return $headings;
    }

    public function registerEvents(): array
    {
        return [
            // handle by a closure.
            AfterSheet::class => function (AfterSheet $event) {
                $statusColumn = Coordinate::stringFromColumnIndex(7);
                $importTypeColumn = Coordinate::stringFromColumnIndex(6);
                $abbreviationColumn = Coordinate::stringFromColumnIndex(3);

                // set dropdown list for first data row
                $statusValidation = $this->getStatusValidation();
                $importTypeValidation = $this->getImportTypeValidation();
                $abbreviationValidation = $this->getTextLengthValidation();

                // clone validation to remaining rows
                for ($index = 2; $index <= $this->totalRow; $index++) {
                    $event->sheet->getCell($statusColumn . $index)->setDataValidation($statusValidation);
                    $event->sheet->getCell($importTypeColumn . $index)->setDataValidation($importTypeValidation);
                    $event->sheet->getCell($abbreviationColumn . $index)->setDataValidation($abbreviationValidation);
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

    protected function getStatusValidation(): DataValidation
    {
        return $this->getDropDownListValidation(BaseStatusEnum::values());
    }

    protected function getDropDownListValidation(array $options): DataValidation
    {
        // set dropdown list for first data row
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle(trans('plugins/location::bulk-import.export.template.input_error'));
        $validation->setError(trans('plugins/location::bulk-import.export.template.value_not_in_list'));
        $validation->setPromptTitle(trans('plugins/location::bulk-import.export.template.pick_from_list'));
        $validation->setPrompt(trans('plugins/location::bulk-import.export.template.prompt_list'));
        $validation->setFormula1(sprintf('"%s"', implode(',', $options)));

        return $validation;
    }

    protected function getImportTypeValidation(): DataValidation
    {
        return $this->getDropDownListValidation(['country', 'state', 'city']);
    }

    protected function getTextLengthValidation(int $max = 2): DataValidation
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_TEXTLENGTH)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle(trans('plugins/location::bulk-import.export.template.input_error'))
            ->setError(trans('plugins/location::bulk-import.export.template.text_not_allowed'))
            ->setPromptTitle(trans('plugins/location::bulk-import.export.template.max_text_length'))
            ->setPrompt(trans('plugins/location::bulk-import.export.template.prompt_max_text_length', compact('max')))
            ->setFormula1((string)$max)
            ->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL);

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
            'E2:E' . $this->totalRow => NumberFormat::FORMAT_TEXT,
            'F2:F' . $this->totalRow => NumberFormat::FORMAT_TEXT,
            'G2:G' . $this->totalRow => NumberFormat::FORMAT_TEXT,
            'H2:H' . $this->totalRow => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 30,
        ];
    }

    protected function getBooleanValidation(): DataValidation
    {
        return $this->getDropDownListValidation(['No', 'Yes']);
    }
}
