<?php

namespace Botble\Ecommerce\Imports;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Maatwebsite\Excel\Validators\Failure;

class ValidateShippingRuleItemImport extends ShippingRuleItemImport
{
    public function model(array $row)
    {
        if ($row['shipping_rule_id'] == 0 && $row['type'] == ShippingRuleTypeEnum::BASED_ON_ZIPCODE && (! $row['zip_code'] || ! $row['city'])) {
            $failures = [];
            if (method_exists($this, 'onFailure')) {
                if (! $row['zip_code']) {
                    $failures[] = new Failure(
                        $this->rowCurrent,
                        'Zip Code',
                        [trans('validation.required', ['attribute' => 'Zip Code'])],
                        []
                    );
                }

                if (! $row['city']) {
                    $failures[] = new Failure(
                        $this->rowCurrent,
                        'City',
                        [trans('validation.required', ['attribute' => 'City'])],
                        []
                    );
                }

                $this->onFailure(...$failures);
            }
        } else {
            $this->onSuccess(collect($row));
        }

        return null;
    }
}
