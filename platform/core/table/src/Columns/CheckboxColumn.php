<?php

namespace Botble\Table\Columns;

use Botble\Base\Facades\Form;

class CheckboxColumn extends Column
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'checkbox', $name)
            ->content('')
            ->title(
                Form::input('checkbox', '', null, [
                    'class' => 'table-check-all',
                    'data-set' => '.dataTable .checkboxes',
                ])->toHtml()
            )
            ->width(20)
            ->alignLeft()
            ->orderable(false)
            ->exportable(false)
            ->searchable(false);
    }
}
