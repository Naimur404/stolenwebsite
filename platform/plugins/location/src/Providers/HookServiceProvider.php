<?php

namespace Botble\Location\Providers;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\FormHelper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Location\Fields\SelectLocationField;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter('form_custom_fields', function (FormAbstract $form, FormHelper $formHelper) {
            if (! $formHelper->hasCustomField('selectLocation')) {
                $form->addCustomField('selectLocation', SelectLocationField::class);
            }

            return $form;
        }, 29, 2);
    }
}
