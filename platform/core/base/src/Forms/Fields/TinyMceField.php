<?php

namespace Botble\Base\Forms\Fields;

use Botble\Base\Forms\FormField;
use Illuminate\Support\Arr;

class TinyMceField extends FormField
{
    protected function getTemplate(): string
    {
        return 'core/base::forms.fields.tinymce';
    }

    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true): string
    {
        $options['class'] = Arr::get($options, 'class', '') . 'form-control editor-tinymce';
        $options['id'] = Arr::get($options, 'id', $this->getName());
        $options['rows'] = Arr::get($options, 'rows', 4);

        return parent::render($options, $showLabel, $showField, $showError);
    }
}
