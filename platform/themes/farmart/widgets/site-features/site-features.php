<?php

use Botble\Widget\AbstractWidget;

class SiteFeaturesWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Site features'),
            'description' => __('Display Site features on sidebar'),
            'data' => [],
            'style' => 'full-width',
        ]);
    }
}
