<?php

namespace Botble\Table\Actions;

class EditAction extends Action
{
    public static function make(string $name = 'edit'): static
    {
        return parent::make($name)
            ->label(trans('core/base::tables.edit'))
            ->color('primary')
            ->icon('fa fa-edit');
    }
}
