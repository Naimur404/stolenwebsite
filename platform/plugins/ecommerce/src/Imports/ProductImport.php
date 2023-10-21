<?php

namespace Botble\Ecommerce\Imports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Models\ProductLabel;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Ecommerce\Services\StoreProductTagService;
use Botble\Media\Facades\RvMedia;
use Botble\Slug\Facades\SlugHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
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
use Mimey\MimeTypes;

class ProductImport implements
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

    protected Request $validatorClass;

    protected Collection $brands;

    protected Collection $categories;

    protected Collection $tags;

    protected Collection $taxes;

    protected Collection $stores;

    protected Collection $labels;

    protected Collection $productCollections;

    protected Collection $productLabels;

    protected string $importType = 'all';

    protected Collection|Model $productAttributeSets;

    protected int $rowCurrent = 1; // include header

    protected Collection $allTaxes;

    protected Collection $barcodes;

    public function __construct(
        protected StoreProductTagService $storeProductTagService,
        protected Request $request
    ) {
        $this->categories = collect();
        $this->brands = collect();
        $this->taxes = collect();
        $this->labels = collect();
        $this->productCollections = collect();
        $this->productLabels = collect();
        $this->productAttributeSets = ProductAttributeSet::query()
            ->with('attributes')
            ->get();
        $this->allTaxes = Tax::query()->get();
        $this->barcodes = collect();

        config(['excel.imports.ignore_empty' => true]);
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

    public function model(array $row): ProductVariation|Product|null
    {
        $importType = $this->getImportType();

        $name = $this->request->input('name');
        $slug = $this->request->input('slug');

        if ($importType == 'products' && $row['import_type'] == 'product') {
            return $this->storeProduct();
        }

        if ($importType == 'variations' && $row['import_type'] == 'variation') {
            $product = $this->getProduct($name, $slug);

            return $this->storeVariant($product);
        }

        if ($row['import_type'] == 'variation') {
            if ($slug) {
                $collection = $this->successes()
                    ->where('import_type', 'product')
                    ->where('slug', $slug)
                    ->last();
            } else {
                $collection = $this->successes()
                    ->where('import_type', 'product')
                    ->where('name', $name)
                    ->last();
            }

            if ($collection) {
                $product = $collection['model'];
            } else {
                $product = $this->getProduct($name, $slug);
            }

            return $this->storeVariant($product);
        }

        return $this->storeProduct();
    }

    protected function getProduct(string $name, string|null $slug): Model|Builder|null
    {
        if ($slug) {
            $slug = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Product::class), Product::class);

            if ($slug) {
                return Product::query()
                    ->where([
                        'id' => $slug->reference_id,
                        'is_variation' => 0,
                    ])
                    ->first();
            }
        }

        return Product::query()
            ->where(function ($query) use ($name) {
                $query
                    ->where('name', $name)
                    ->orWhere('id', $name);
            })
            ->where('is_variation', 0)
            ->first();
    }

    public function storeProduct(): ?Product
    {
        $product = new Product();

        $this->request->merge(['images' => $this->getImageURLs((array)$this->request->input('images', []))]);

        if ($description = $this->request->input('description')) {
            $this->request->merge(['description' => BaseHelper::clean($description)]);
        }

        if ($content = $this->request->input('content')) {
            $this->request->merge(['content' => BaseHelper::clean($content)]);
        }

        $product->status = strtolower($this->request->input('status'));

        $product = (new StoreProductService())->execute($this->request, $product);

        $tagsInput = (array)$this->request->input('tags', []);
        if ($tagsInput) {
            $tags = [];
            foreach ($tagsInput as $tag) {
                $tags[] = ['value' => $tag];
            }
            $this->request->merge(['tag' => json_encode($tags)]);
            $this->storeProductTagService->execute($this->request, $product);
        }

        $attributeSets = $this->request->input('attribute_sets', []);

        $product->productAttributeSets()->sync($attributeSets);

        $collect = collect([
            'name' => $product->name,
            'slug' => $this->request->input('slug'),
            'import_type' => 'product',
            'attribute_sets' => $attributeSets,
            'model' => $product,
        ]);

        $this->onSuccess($collect);

        return $product;
    }

    protected function getImageURLs(array $images): array
    {
        $images = array_values(array_filter($images));

        foreach ($images as $key => $image) {
            $images[$key] = str_replace(RvMedia::getUploadURL() . '/', '', trim($image));

            if (Str::startsWith($images[$key], ['http://', 'https://'])) {
                $images[$key] = $this->uploadImageFromURL($images[$key]);
            }
        }

        return $images;
    }

    protected function uploadImageFromURL(string|null $url): string|null
    {
        if (empty($url)) {
            return $url;
        }

        $info = pathinfo($url);

        try {
            $contents = file_get_contents($url);
        } catch (Exception) {
            return $url;
        }

        if (empty($contents)) {
            return $url;
        }

        $path = '/tmp';

        if (! File::isDirectory($path)) {
            File::makeDirectory($path);
        }

        $path = $path . '/' . $info['basename'];

        file_put_contents($path, $contents);

        $mimeType = (new MimeTypes())->getMimeType(File::extension($url));

        $fileUpload = new UploadedFile($path, $info['basename'], $mimeType, null, true);

        $result = RvMedia::handleUpload($fileUpload, 0, 'products');

        File::delete($path);

        if (! $result['error']) {
            $url = $result['data']->url;
        }

        return $url;
    }

    public function storeVariant($product): ?ProductVariation
    {
        if (! $product) {
            if (method_exists($this, 'onFailure')) {
                $failures[] = new Failure(
                    $this->rowCurrent,
                    'Product Name',
                    [__('Product name ":name" does not exists', ['name' => $this->request->input('name')])],
                    []
                );
                $this->onFailure(...$failures);
            }

            return null;
        }

        $addedAttributes = $this->request->input('attribute_sets', []);
        $result = ProductVariation::getVariationByAttributesOrCreate($product->id, $addedAttributes);
        if (! $result['created']) {
            if (method_exists($this, 'onFailure')) {
                $failures[] = new Failure(
                    $this->rowCurrent,
                    'variation',
                    [
                        trans('plugins/ecommerce::products.form.variation_existed') . ' ' . trans(
                            'plugins/ecommerce::products.form.product_id'
                        ) . ': ' . $product->id,
                    ],
                    []
                );
                $this->onFailure(...$failures);
            }

            return null;
        }

        $variation = $result['variation'];

        $version = array_merge($variation->toArray(), $this->request->toArray());
        $version['variation_default_id'] = Arr::get($version, 'is_variation_default') ? $version['id'] : null;
        $version['attribute_sets'] = $addedAttributes;

        if ($version['description']) {
            $version['description'] = BaseHelper::clean($version['description']);
        }

        if ($version['content']) {
            $version['content'] = BaseHelper::clean($version['content']);
        }

        $productRelatedToVariation = new Product();
        $productRelatedToVariation->fill($version);

        $productRelatedToVariation->name = $product->name;
        $productRelatedToVariation->status = $product->status;
        $productRelatedToVariation->brand_id = $product->brand_id;
        $productRelatedToVariation->is_variation = 1;

        $productRelatedToVariation->sku = Arr::get($version, 'sku');
        if (! $productRelatedToVariation->sku && Arr::get($version, 'auto_generate_sku')) {
            $productRelatedToVariation->sku = $product->sku;
            foreach ($version['attribute_sets'] as $setId => $attributeId) {
                $attributeSet = $this->productAttributeSets->firstWhere('id', $setId);
                if ($attributeSet) {
                    $attribute = $attributeSet->attributes->firstWhere('id', $attributeId);
                    if ($attribute) {
                        $productRelatedToVariation->sku .= '-' . Str::upper($attribute->slug);
                    }
                }
            }
        }

        $productRelatedToVariation->price = Arr::get($version, 'price', $product->price);
        $productRelatedToVariation->sale_price = Arr::get($version, 'sale_price', $product->sale_price);

        if (Arr::get($version, 'description')) {
            $productRelatedToVariation->description = BaseHelper::clean($version['description']);
        }

        if (Arr::get($version, 'content')) {
            $productRelatedToVariation->content = BaseHelper::clean($version['content']);
        }

        $productRelatedToVariation->length = Arr::get($version, 'length', $product->length);
        $productRelatedToVariation->wide = Arr::get($version, 'wide', $product->wide);
        $productRelatedToVariation->height = Arr::get($version, 'height', $product->height);
        $productRelatedToVariation->weight = Arr::get($version, 'weight', $product->weight);

        $productRelatedToVariation->sale_type = (int)Arr::get($version, 'sale_type', $product->sale_type);

        if ($productRelatedToVariation->sale_type == 0) {
            $productRelatedToVariation->start_date = null;
            $productRelatedToVariation->end_date = null;
        } else {
            $productRelatedToVariation->start_date = Carbon::parse(
                Arr::get($version, 'start_date', $product->start_date)
            )->toDateTimeString();
            $productRelatedToVariation->end_date = Carbon::parse(
                Arr::get($version, 'end_date', $product->end_date)
            )->toDateTimeString();
        }

        $productRelatedToVariation->images = json_encode(
            $this->getImageURLs((array)Arr::get($version, 'images', []) ?: [])
        );

        $productRelatedToVariation->status = strtolower(Arr::get($version, 'status', $product->status));

        $productRelatedToVariation->product_type = $product->product_type;
        $productRelatedToVariation->save();

        event(new CreatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, $this->request, $productRelatedToVariation));

        $variation->product_id = $productRelatedToVariation->id;

        $variation->is_default = Arr::get($version, 'variation_default_id', 0) == $variation->id;

        $variation->save();

        if ($version['attribute_sets']) {
            $variation->productAttributes()->sync($version['attribute_sets']);
        }

        $this->onSuccess(
            collect([
                'name' => $variation->name,
                'slug' => '',
                'import_type' => 'variation',
                'attribute_sets' => [],
                'model' => $variation,
            ])
        );

        return $variation;
    }

    public function map($row): array
    {
        ++$this->rowCurrent;
        $row = $this->mapLocalization($row);
        $row = $this->setCategoriesToRow($row);
        $row = $this->setBrandToRow($row);
        $row = $this->setTaxToRow($row);
        $row = $this->setProductCollectionsToRow($row);
        $row = $this->setProductLabelsToRow($row);

        $row = apply_filters('ecommerce_import_product_row_data', $row);

        $this->request->merge($row);

        return $row;
    }

    protected function setTaxToRow(array $row): array
    {
        $row['tax_id'] = null;

        $taxIds = [];
        if (! empty($row['tax'])) {
            $tax = $this->getTaxByKeyword(trim($row['tax']));
            if ($tax) {
                $taxIds[] = $tax->getKey();
            }
        }

        if ($row['taxes']) {
            foreach ($row['taxes'] as $value) {
                $tax = $this->getTaxByKeyword(trim($value));
                if ($tax) {
                    $taxIds[] = $tax->getKey();
                }
            }

            $row['taxes'] = array_filter($taxIds);
        }

        return $row;
    }

    protected function getTaxByKeyword(string|int $keyword): Tax|null
    {
        return $this->allTaxes->filter(function ($item) use ($keyword) {
            if (is_numeric($keyword)) {
                return $item->id == $keyword;
            }

            return $item->title == $keyword;
        })->first();
    }

    protected function setBrandToRow(array $row): array
    {
        $row['brand_id'] = 0;

        if (! empty($row['brand'])) {
            $row['brand'] = trim($row['brand']);

            $brand = $this->brands->firstWhere('keyword', $row['brand']);
            if ($brand) {
                $brandId = $brand['brand_id'];
            } else {
                if (is_numeric($row['brand'])) {
                    $brand = Brand::query()->find($row['brand']);
                } else {
                    $brand = Brand::query()->where('name', $row['brand'])->first();
                }

                $brandId = $brand ? $brand->get() : 0;
                $this->brands->push([
                    'keyword' => $row['brand'],
                    'brand_id' => $brandId,
                ]);
            }

            $row['brand_id'] = $brandId;
        }

        return $row;
    }

    protected function setCategoriesToRow(array $row): array
    {
        if ($row['categories']) {
            $categories = $row['categories'];
            $categoryIds = [];
            foreach ($categories as $value) {
                $value = trim($value);

                $category = $this->categories->firstWhere('keyword', $value);
                if ($category) {
                    $categoryId = $category['category_id'];
                } else {
                    if (is_numeric($value)) {
                        $category = ProductCategory::query()->find($value);
                    } else {
                        $category = ProductCategory::query()->where('name', $value)->first();
                    }

                    $categoryId = $category ? $category->getKey() : 0;
                    $this->categories->push([
                        'keyword' => $value,
                        'category_id' => $categoryId,
                    ]);
                }
                $categoryIds[] = $categoryId;
            }

            $row['categories'] = array_filter($categoryIds);
        }

        return $row;
    }

    protected function setProductCollectionsToRow(array $row): array
    {
        if ($row['product_collections']) {
            $productCollections = $row['product_collections'];
            $collectionIds = [];
            foreach ($productCollections as $value) {
                $value = trim($value);

                $collection = $this->productCollections->firstWhere('keyword', $value);
                if ($collection) {
                    $collectionId = $collection['collection_id'];
                } else {
                    if (is_numeric($value)) {
                        $collection = ProductCollection::query()->find($value);
                    } else {
                        $collection = ProductCollection::query()->where('name', $value)->first();
                    }

                    $collectionId = $collection ? $collection->getKey() : 0;
                    $this->productCollections->push([
                        'keyword' => $value,
                        'collection_id' => $collectionId,
                    ]);
                }
                $collectionIds[] = $collectionId;
            }

            $row['product_collections'] = array_filter($collectionIds);
        }

        return $row;
    }

    protected function setProductLabelsToRow(array $row): array
    {
        if ($row['product_labels']) {
            $productLabels = $row['product_labels'];
            $productLabelIds = [];
            foreach ($productLabels as $value) {
                $value = trim($value);

                $productLabel = $this->productLabels->firstWhere('keyword', $value);
                if ($productLabel) {
                    $productLabelId = $productLabel['product_label_id'];
                } else {
                    if (is_numeric($value)) {
                        $productLabel = ProductLabel::query()->find($value);
                    } else {
                        $productLabel = ProductLabel::query()->where('name', $value)->first();
                    }

                    $productLabelId = $productLabel ? $productLabel->getKey() : 0;
                    $this->productLabels->push([
                        'keyword' => $value,
                        'product_label_id' => $productLabelId,
                    ]);
                }
                $productLabelIds[] = $productLabelId;
            }

            $row['product_labels'] = array_filter($productLabelIds);
        }

        return $row;
    }

    public function mapLocalization(array $row): array
    {
        $row['stock_status'] = (string)Arr::get($row, 'stock_status');
        if (! in_array($row['stock_status'], StockStatusEnum::toArray())) {
            $row['stock_status'] = StockStatusEnum::IN_STOCK;
        }

        $row['status'] = Arr::get($row, 'status');
        if (! in_array($row['status'], BaseStatusEnum::toArray())) {
            $row['status'] = BaseStatusEnum::PENDING;
        }

        $row['product_type'] = Arr::get($row, 'product_type');
        if (! in_array($row['product_type'], ProductTypeEnum::toArray())) {
            $row['product_type'] = ProductTypeEnum::PHYSICAL;
        }

        $row['import_type'] = Arr::get($row, 'import_type');
        if ($row['import_type'] != 'variation') {
            $row['import_type'] = 'product';
        }

        $row['name'] = Arr::get($row, 'product_name');
        $row['is_slug_editable'] = true;

        $this->setValues($row, [
            ['key' => 'slug', 'type' => 'string', 'default' => 'name'],
            ['key' => 'sku', 'type' => 'string'],
            ['key' => 'price', 'type' => 'number'],
            ['key' => 'weight', 'type' => 'number'],
            ['key' => 'length', 'type' => 'number'],
            ['key' => 'wide', 'type' => 'number'],
            ['key' => 'height', 'type' => 'number'],
            ['key' => 'cost_per_item', 'type' => 'number'],
            ['key' => 'barcode', 'type' => 'string'],
            ['key' => 'is_featured', 'type' => 'bool'],
            ['key' => 'product_labels'],
            ['key' => 'labels'],
            ['key' => 'images'],
            ['key' => 'categories'],
            ['key' => 'product_collections'],
            ['key' => 'product_attributes'],
            ['key' => 'is_variation_default', 'type' => 'bool'],
            ['key' => 'auto_generate_sku', 'type' => 'bool'],
            ['key' => 'with_storehouse_management', 'type' => 'bool'],
            ['key' => 'allow_checkout_when_out_of_stock', 'type' => 'bool'],
            ['key' => 'quantity', 'type' => 'number'],
            ['key' => 'sale_price', 'type' => 'number'],
            ['key' => 'start_date', 'type' => 'datetime', 'from' => 'start_date_sale_price'],
            ['key' => 'end_date', 'type' => 'datetime', 'from' => 'end_date_sale_price'],
            ['key' => 'tags'],
            ['key' => 'taxes'],
        ]);

        $row['product_labels'] = $row['labels'];

        if ($row['import_type'] == 'product' && ! $row['sku'] && $row['auto_generate_sku']) {
            $row['sku'] = Str::upper(Str::random(7));
        }

        $row['sale_type'] = 0;
        if ($row['start_date'] || $row['end_date']) {
            $row['sale_type'] = 1;
        }

        if (! $row['with_storehouse_management']) {
            $row['quantity'] = null;
            $row['allow_checkout_when_out_of_stock'] = false;
        }

        $attributeSets = Arr::get($row, 'product_attributes');
        $row['attribute_sets'] = [];
        $row['product_attributes'] = [];

        if ($row['import_type'] == 'variation') {
            foreach ($attributeSets as $attrSet) {
                $attrSet = explode(':', $attrSet);
                $title = Arr::get($attrSet, 0);
                $valueX = Arr::get($attrSet, 1);

                $attribute = $this->productAttributeSets->filter(function ($value) use ($title) {
                    return $value['title'] == $title;
                })->first();

                if ($attribute) {
                    $attr = $attribute->attributes->filter(function ($value) use ($valueX) {
                        return $value['title'] == $valueX;
                    })->first();

                    if ($attr) {
                        $row['attribute_sets'][$attribute->id] = $attr->id;
                    }
                }
            }
        }

        if ($row['import_type'] == 'product') {
            foreach ($attributeSets as $attrSet) {
                $attribute = $this->productAttributeSets->filter(function ($value) use ($attrSet) {
                    return $value['title'] == $attrSet;
                })->first();

                if ($attribute) {
                    $row['attribute_sets'][] = $attribute->id;
                }
            }
        }

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
                $value = (bool)$value;

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

        if ($value && $key == 'barcode') {
            if ($barcode = $this->barcodes->firstWhere('value', $value)) {
                if (method_exists($this, 'onFailure')) {
                    $failures[] = new Failure(
                        $this->rowCurrent,
                        'Barcode',
                        [
                            __(
                                'Barcode ":value" has been duplicated on row #:row',
                                ['value' => $value, 'row' => Arr::get($barcode, 'row')]
                            ),
                        ],
                        [$value]
                    );
                    $this->onFailure(...$failures);
                }
            } else {
                $this->barcodes->push(['row' => $this->rowCurrent, 'value' => $value]);
            }
        }

        return $this;
    }

    public function rules(): array
    {
        return method_exists($this->getValidatorClass(), 'rules') ? $this->getValidatorClass()->rules() : [];
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
