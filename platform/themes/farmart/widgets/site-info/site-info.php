<?php

use Botble\Widget\AbstractWidget;

class SiteInfoWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Site information'),
            'description' => __('Widget display site information'),
            'about' => null,
            'address' => null,
            'phone' => null,
            'email' => null,
            'working_time' => null,
        ]);
    }
}
