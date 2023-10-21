<?php

namespace Botble\Ecommerce\Imports;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Maatwebsite\Excel\Validators\Failure;

class ValidateProductImport extends ProductImport
{
    public function model(array $row): ProductVariation|Product|null
    {
        $importType = $this->getImportType();

        $name = $this->request->input('name');
        $slug = $this->request->input('slug');

        if ($importType == 'products' && $row['import_type'] == 'product') {
            return $this->storeProduction();
        }

        if ($importType == 'variations' && $row['import_type'] == 'variation') {
            $product = $this->getProduct($name, $slug);

            return $this->storeVariant($product);
        }

        if ($row['import_type'] == 'variation') {
            $collection = $this->successes()
                ->where('import_type', 'product')
                ->where('name', $name)
                ->first();

            if ($collection) {
                $product = $collection['model'];
            } else {
                $product = $this->getProduct($name, $slug);
            }

            return $this->storeVariant($product);
        }

        return $this->storeProduction();
    }

    public function storeProduction()
    {
        $product = collect($this->request->all());
        $collect = collect([
            'name' => $product['name'],
            'import_type' => 'product',
            'model' => $product,
        ]);

        $this->onSuccess($collect);

        return null;
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

        return null;
    }
}
