<?php

use Botble\Widget\AbstractWidget;

class NewsletterWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Newsletter form'),
            'description' => __('Display Newsletter form on sidebar'),
            'title' => __('Stay home & get your daily <br />needs from our shop'),
            'subtitle' => __('Start Your Daily Shopping with <span>Nest Mart</span>'),
            'image' => null,
            'background_image' => null,
        ]);
    }
}
