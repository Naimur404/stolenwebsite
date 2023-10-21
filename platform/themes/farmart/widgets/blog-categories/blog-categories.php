<?php

use Botble\Widget\AbstractWidget;

class BlogCategoriesWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Blog Categories'),
            'description' => __('Widget display blog categories'),
            'number_display' => 10,
        ]);
    }
}
