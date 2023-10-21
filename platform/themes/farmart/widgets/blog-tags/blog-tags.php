<?php

use Botble\Widget\AbstractWidget;

class BlogTagsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Blog Tags'),
            'description' => __('Blog - Popular tags'),
            'number_display' => 5,
        ]);
    }
}
