<?php

namespace Botble\Ecommerce\Forms\Fields;

use Botble\Base\Forms\FormField;

class CategoryMultiField extends FormField
{
    protected function getTemplate(): string
    {
        return 'plugins/ecommerce::product-categories.partials.categories-multi';
    }
}
