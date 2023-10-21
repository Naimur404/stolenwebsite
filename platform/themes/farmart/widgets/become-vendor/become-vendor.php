<?php

use Botble\Widget\AbstractWidget;

class BecomeVendorWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Become a Vendor?'),
            'description' => __('Display Become a vendor on product detail sidebar'),
        ]);
    }
}
