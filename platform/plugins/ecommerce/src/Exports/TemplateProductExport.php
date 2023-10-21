<?php

namespace Botble\Ecommerce\Exports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Ecommerce\Models\Tax;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

class TemplateProductExport implements
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

    protected Collection $brands;

    protected bool $enabledDigital;

    protected bool $isMarketplaceActive;

    public function __construct(string $exportType = Excel::XLSX)
    {
        $this->exportType = $exportType;

        $productNames = collect([
            'Bread - Sour Sticks With Onion',
            'Cheese - Cheddar, Mild',
            'Creme De Banane - Marie',
        ]);

        $descriptions = collect([
            'Praesent blandit. Nam nulla. Integer pede justo, lacinia eget, tincidunt eget, tempus vel, pede.',
            'Proin eu mi. Nulla ac enim. In tempor, turpis nec euismod scelerisque, quam turpis adipiscing lorem.',
            'Cras mi pede, malesuada in, imperdiet et, commodo vulputate, justo. In blandit ultrices enim.',
        ]);

        $productName = $productNames->random();

        $categories = ProductCategory::query()->inRandomOrder()->limit(2)->get();
        $brands = Brand::query()->pluck('name', 'id')->all();
        $this->brands = collect($brands);
        $taxes = Tax::query()->inRandomOrder()->limit(2)->get();

        $productTags = ProductTag::query()->inRandomOrder()->limit(2)->get();

        $productAttributeSets = ProductAttributeSet::query()->inRandomOrder()->limit(2)->get();
        $price = rand(20, 100);

        $attributeSets = $productAttributeSets->sortByDesc('order');

        $this->isMarketplaceActive = is_plugin_active('marketplace');

        $this->enabledDigital = EcommerceHelper::isEnabledSupportDigitalProducts();

        $product = array_replace($this->getTempProductData(), [
            'name' => $productName,
            'description' => $descriptions->random(),
            'sku' => Str::upper(Str::random(7)),
            'categories' => implode(',', $categories->pluck('name')->all()),
            'status' => BaseStatusEnum::PUBLISHED,
            'is_featured' => Arr::random(['Yes', 'No']),
            'brand' => $this->brands->count() ? $this->brands->random() : null,
            'taxes' => implode(',', $taxes->pluck('title')->all()),
            'images' => 'products/1.jpg',
            'price' => $price,
            'product_attributes' => implode(',', $attributeSets->pluck('title')->all()),
            'import_type' => 'product',
            'tags' => implode(',', $productTags->pluck('name')->all()),
        ]);

        if ($this->enabledDigital) {
            $product['product_type'] = ProductTypeEnum::PHYSICAL;
        }

        if ($this->isMarketplaceActive) {
            $stores = DB::table('mp_stores')->pluck('name', 'id')->all();
            $stores = collect($stores);

            $product['vendor'] = $stores->count() ? $stores->random() : null;
        }

        $attributes1 = [];
        foreach ($attributeSets as $set) {
            $attributes1[] = $set->title . ':' . ($set->attributes->count() ? $set->attributes->random()->title : null);
        }

        $salePrice = $price - rand(2, 5);
        $productVariation1 = array_replace($this->getTempProductData(), [
            'name' => $productName,
            'auto_generate_sku' => 'Yes',
            'status' => BaseStatusEnum::PUBLISHED,
            'is_featured' => Arr::random(['Yes', 'No']),
            'images' => 'products/1.jpg,products/2.jpg',
            'price' => $price,
            'product_attributes' => implode(',', $attributes1),
            'import_type' => 'variation',
            'is_variation_default' => 'Yes',
            'stock_status' => StockStatusEnum::IN_STOCK,
            'with_storehouse_management' => 'Yes',
            'quantity' => rand(20, 300),
            'sale_price' => $salePrice,
            'start_date_sale_price' => Carbon::now()->startOfDay()->format('Y-m-d H:i:s'),
            'end_date_sale_price' => Carbon::now()->addDays(20)->endOfDay()->format('Y-m-d H:i:s'),
            'weight' => rand(20, 300),
            'length' => rand(20, 300),
            'wide' => rand(20, 300),
            'height' => rand(20, 300),
            'cost_per_item' => $salePrice - rand(2, 3),
            'barcode' => mt_rand(1000000000, 9999999999),
        ]);

        $attributes2 = [];
        foreach ($attributeSets as $set) {
            $attr = $set->title . ':' . ($set->attributes->count() ? $set->attributes->random()->title : null);

            if (in_array($attr, $attributes1)) {
                $attr = $set->title . ':' . ($set->attributes->count() ? $set->attributes->random()->title : null);
            }

            $attributes2[] = $attr;
        }

        $productVariation2 = array_replace($this->getTempProductData(), [
            'name' => $productName,
            'auto_generate_sku' => 'Yes',
            'status' => BaseStatusEnum::PUBLISHED,
            'is_featured' => Arr::random(['Yes', 'No']),
            'images' => 'products/1.jpg,products/3.jpg',
            'price' => $price,
            'product_attributes' => implode(',', $attributes2),
            'import_type' => 'variation',
            'is_variation_default' => 'No',
            'stock_status' => StockStatusEnum::IN_STOCK,
            'with_storehouse_management' => 'No',
            'sale_price' => $price,
            'weight' => rand(20, 300),
            'length' => rand(20, 300),
            'wide' => rand(20, 300),
            'height' => rand(20, 300),
            'cost_per_item' => $price - rand(2, 3),
            'barcode' => mt_rand(1000000000, 9999999999),
        ]);

        $this->results = collect([
            $product,
            $productVariation1,
            $productVariation2,
        ]);

        $this->totalRow = $exportType == Excel::XLSX ? 100 : ($this->results->count() + 1);
    }

    public function collection(): Collection
    {
        return $this->results;
    }

    public function headings(): array
    {
        $headings = [
            'name' => 'Product name',
            'description' => 'Description',
            'slug' => 'Slug',
            'sku' => 'SKU',
            'auto_generate_sku' => 'Auto Generate SKU',
            'categories' => 'Categories',
            'status' => 'Status',
            'is_featured' => 'Is featured?',
            'brand' => 'Brand',
            'product_collections' => 'Product collections',
            'labels' => 'Labels',
            'taxes' => 'Taxes',
            'images' => 'Images',
            'price' => 'Price',
            'product_attributes' => 'Product attributes',
            'import_type' => 'Import type',
            'is_variation_default' => 'Is variation default?',
            'stock_status' => 'Stock status',
            'with_storehouse_management' => 'With storehouse management',
            'quantity' => 'Quantity',
            'allow_checkout_when_out_of_stock' => 'Allow checkout when out of stock',
            'sale_price' => 'Sale price',
            'start_date_sale_price' => 'Start date sale price',
            'end_date_sale_price' => 'End date sale price',
            'weight' => 'Weight',
            'length' => 'Length',
            'wide' => 'Wide',
            'height' => 'Height',
            'cost_per_item' => 'Cost per item',
            'barcode' => 'Barcode',
            'content' => 'Content',
            'tags' => 'Tags',
        ];

        if ($this->enabledDigital) {
            $headings['product_type'] = 'Product type';
        }

        if ($this->isMarketplaceActive) {
            $headings['vendor'] = 'Vendor';
        }

        return $headings;
    }

    public function getTempProductData(): array
    {
        return array_fill_keys(array_keys($this->headings()), '');
    }

    public function stringFromColumnIndex(string $column): string
    {
        $key = array_search($column, array_keys($this->headings()));

        if ($key !== false) {
            return Coordinate::stringFromColumnIndex($key + 1);
        }

        return '';
    }

    public function registerEvents(): array
    {
        return [
            // handle by a closure.
            AfterSheet::class => function (AfterSheet $event) {
                $statusColumn = $this->stringFromColumnIndex('status');
                $stockColumn = $this->stringFromColumnIndex('stock_status');
                $autoGenerateSKUColumn = $this->stringFromColumnIndex('auto_generate_sku');
                $isFeaturedColumn = $this->stringFromColumnIndex('is_featured');
                $brandColumn = $this->stringFromColumnIndex('brand');

                $importTypeColumn = $this->stringFromColumnIndex('import_type');
                $isVariationDefaultColumn = $this->stringFromColumnIndex('is_variation_default');
                $withStorehouseManagementColumn = $this->stringFromColumnIndex('with_storehouse_management');
                $allowCheckoutWhenOutOfStockColumn = $this->stringFromColumnIndex('allow_checkout_when_out_of_stock');
                $quantityColumn = $this->stringFromColumnIndex('quantity');
                $priceColumn = $this->stringFromColumnIndex('price');
                $saleColumn = $this->stringFromColumnIndex('sale_price');
                $weightColumn = $this->stringFromColumnIndex('weight');
                $lengthColumn = $this->stringFromColumnIndex('length');
                $wideColumn = $this->stringFromColumnIndex('wide');
                $heightColumn = $this->stringFromColumnIndex('height');
                $costPerItemColumn = $this->stringFromColumnIndex('cost_per_item');
                $productTypeColumn = $this->stringFromColumnIndex('product_type');

                // set dropdown list for first data row
                $statusValidation = $this->getStatusValidation();
                $stockValidation = $this->getStockValidation();
                $booleanValidation = $this->getBooleanValidation();
                $importTypeValidation = $this->getImportTypeValidation();
                $wholeNumberValidation = $this->getWholeNumberValidation();
                $decimalValidation = $this->getDecimalValidation();
                $brandValidation = $this->getBrandValidation();

                $productTypeValidation = $this->getProductTypeValidation();

                // clone validation to remaining rows
                for ($index = 2; $index <= $this->totalRow; $index++) {
                    $event->sheet->getCell($statusColumn . $index)->setDataValidation($statusValidation);
                    $event->sheet->getCell($stockColumn . $index)->setDataValidation($stockValidation);
                    $event->sheet->getCell($autoGenerateSKUColumn . $index)->setDataValidation($booleanValidation);
                    $event->sheet->getCell($isFeaturedColumn . $index)->setDataValidation($booleanValidation);
                    $event->sheet->getCell($brandColumn . $index)->setDataValidation($brandValidation);

                    $event->sheet->getCell($importTypeColumn . $index)->setDataValidation($importTypeValidation);
                    $event->sheet->getCell($isVariationDefaultColumn . $index)->setDataValidation($booleanValidation);
                    $event->sheet->getCell($withStorehouseManagementColumn . $index)
                        ->setDataValidation($booleanValidation);
                    $event->sheet->getCell($allowCheckoutWhenOutOfStockColumn . $index)
                        ->setDataValidation($booleanValidation);

                    $event->sheet->getCell($quantityColumn . $index)->setDataValidation($wholeNumberValidation);

                    $event->sheet->getCell($weightColumn . $index)->setDataValidation($decimalValidation);
                    $event->sheet->getCell($lengthColumn . $index)->setDataValidation($decimalValidation);
                    $event->sheet->getCell($wideColumn . $index)->setDataValidation($decimalValidation);
                    $event->sheet->getCell($heightColumn . $index)->setDataValidation($decimalValidation);
                    $event->sheet->getCell($saleColumn . $index)->setDataValidation($decimalValidation);
                    $event->sheet->getCell($priceColumn . $index)->setDataValidation($decimalValidation);
                    $event->sheet->getCell($costPerItemColumn . $index)->setDataValidation($decimalValidation);

                    if ($this->enabledDigital) {
                        $event->sheet->getCell($productTypeColumn . $index)->setDataValidation($productTypeValidation);
                    }
                }

                $delegate = $event->sheet->getDelegate();
                foreach ($this->columnFormats() as $column => $format) {
                    $delegate
                        ->getStyle($column)
                        ->getNumberFormat()
                        ->setFormatCode($format);
                }

                $delegate->getStyle('A1'); // Reset selected
            },
        ];
    }

    protected function getStatusValidation(): DataValidation
    {
        return $this->getDropDownListValidation(BaseStatusEnum::values());
    }

    protected function getProductTypeValidation(): DataValidation
    {
        return $this->getDropDownListValidation(ProductTypeEnum::values());
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
        $validation->setErrorTitle(trans('plugins/ecommerce::bulk-import.export.template.input_error'));
        $validation->setError(trans('plugins/ecommerce::bulk-import.export.template.value_not_in_list'));
        $validation->setPromptTitle(trans('plugins/ecommerce::bulk-import.export.template.pick_from_list'));
        $validation->setPrompt(trans('plugins/ecommerce::bulk-import.export.template.prompt_list'));
        $validation->setFormula1(sprintf('"%s"', implode(',', $options)));

        return $validation;
    }

    protected function getStockValidation(): DataValidation
    {
        return $this->getDropDownListValidation(StockStatusEnum::values());
    }

    protected function getBooleanValidation(): DataValidation
    {
        return $this->getDropDownListValidation(['No', 'Yes']);
    }

    protected function getImportTypeValidation(): DataValidation
    {
        return $this->getDropDownListValidation(['product', 'variation']);
    }

    protected function getWholeNumberValidation(int $min = 0): DataValidation
    {
        // set dropdown list for first data row
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_WHOLE);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle(trans('plugins/ecommerce::bulk-import.export.template.input_error'));
        $validation->setError(trans('plugins/ecommerce::bulk-import.export.template.number_not_allowed'));
        $validation->setPromptTitle(trans('plugins/ecommerce::bulk-import.export.template.allowed_input'));
        $validation->setPrompt(trans(
            'plugins/ecommerce::bulk-import.export.template.prompt_whole_number',
            compact('min')
        ));
        $validation->setFormula1((string)$min);
        $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);

        return $validation;
    }

    protected function getDecimalValidation(int $min = 0): DataValidation
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
        $validation->setPrompt(trans('plugins/ecommerce::bulk-import.export.template.prompt_decimal', compact('min')));
        $validation->setFormula1((string)$min);
        $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);

        return $validation;
    }

    protected function getBrandValidation(): DataValidation
    {
        return $this->getDropDownListValidation(['-- None --'] + $this->brands->toArray());
    }

    public function columnFormats(): array
    {
        if ($this->exportType != Excel::XLSX) {
            return [];
        }

        $columns = [
            'name' => NumberFormat::FORMAT_TEXT,
            'description' => NumberFormat::FORMAT_TEXT,
            'slug' => NumberFormat::FORMAT_TEXT,
            'sku' => NumberFormat::FORMAT_TEXT,
            'categories' => NumberFormat::FORMAT_TEXT,
            'product_collections' => NumberFormat::FORMAT_TEXT,
            'labels' => NumberFormat::FORMAT_TEXT,
            'taxes' => NumberFormat::FORMAT_TEXT,
            'images' => NumberFormat::FORMAT_TEXT,
            'price' => NumberFormat::FORMAT_NUMBER_00,
            'quantity' => NumberFormat::FORMAT_NUMBER,
            'sale_price' => NumberFormat::FORMAT_NUMBER_00,
            'start_date_sale_price' => 'yyyy-mm-dd hh:mm:ss',
            'end_date_sale_price' => 'yyyy-mm-dd hh:mm:ss',
            'weight' => NumberFormat::FORMAT_GENERAL,
            'length' => NumberFormat::FORMAT_GENERAL,
            'wide' => NumberFormat::FORMAT_GENERAL,
            'height' => NumberFormat::FORMAT_GENERAL,
            'cost_per_item' => NumberFormat::FORMAT_NUMBER_00,
        ];

        $formatted = [];
        foreach ($columns as $key => $value) {
            $column = $this->stringFromColumnIndex($key);
            $formatted[$column . '2:' . $column . $this->totalRow] = $value;
        }

        return $formatted;
    }

    public function columnWidths(): array
    {
        return [
            $this->stringFromColumnIndex('name') => 25,
            $this->stringFromColumnIndex('description') => 30,
        ];
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:220',
            'description' => 'nullable|string|max:400',
            'slug' => 'nullable',
            'sku' => 'nullable|multiple',
            'auto_generate_sku' => 'nullable|string (Yes or No)|default: Yes',
            'categories' => 'nullable|multiple',
            'status' => 'required|enum:' . implode(',', BaseStatusEnum::values()) . '|default:' . BaseStatusEnum::PENDING,
            'is_featured' => 'nullable|string (Yes or No)|default: No',
            'brand' => 'nullable|[Brand name | Brand ID]',
            'product_collections' => 'nullable|[Product collection name | Product collection ID]|multiple',
            'labels' => 'nullable|[Product label name | Product label ID]|multiple',
            'taxes' => 'nullable|[Tax title | Tax ID]|multiple',
            'images' => 'nullable|string|multiple',
            'price' => 'nullable|number',
            'product_attributes' => 'nullable|string',
            'import_type' => 'nullable|enum:product,variation|default:product',
            'is_variation_default' => 'nullable|bool|default:false',
            'stock_status' => 'nullable|enum:' . implode(',', StockStatusEnum::values()) . '|default:' . StockStatusEnum::IN_STOCK,
            'with_storehouse_management' => 'nullable|bool|default:0',
            'quantity' => 'nullable|number',
            'allow_checkout_when_out_of_stock' => 'nullable|bool|default:0',
            'sale_price' => 'nullable|number',
            'start_date_sale_price' => 'nullable|datetime|date_format:Y-m-d H:i:s',
            'end_date_sale_price' => 'nullable|datetime|date_format:Y-m-d H:i:s',
            'weight' => 'nullable|number',
            'length' => 'nullable|number',
            'wide' => 'nullable|number',
            'height' => 'nullable|number',
            'cost_per_item' => 'nullable|numeric|min:0|max:100000000000',
            'barcode' => 'nullable|max:50|unique:products',
            'content' => 'nullable',
            'tags' => 'nullable|[Product tag name]|multiple',
        ];

        if ($this->enabledDigital) {
            $rules['product_type'] = 'nullable|enum:' . implode(',', ProductTypeEnum::values()) . '|default:' . ProductTypeEnum::PHYSICAL;
        }

        if ($this->isMarketplaceActive) {
            $rules['vendor'] = 'nullable|[Vendor name | Vendor ID]';
        }

        return $rules;
    }
}
