<?php

app()->booted(function () {
    theme_option()
        ->setField([
            'id' => 'facebook_comment_enabled_in_product',
            'section_id' => 'opt-text-subsection-facebook-integration',
            'type' => 'customSelect',
            'label' => __('Enable Facebook comment in the product page?'),
            'attributes' => [
                'name' => 'facebook_comment_enabled_in_product',
                'list' => [
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ],
                'value' => 'no',
            ],
        ]);
});
