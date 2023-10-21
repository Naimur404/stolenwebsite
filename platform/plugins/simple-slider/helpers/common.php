<?php

use Botble\SimpleSlider\Models\SimpleSlider;
use Illuminate\Database\Eloquent\Collection;

if (! function_exists('get_all_simple_sliders')) {
    /**
     * @deprecated
     */
    function get_all_simple_sliders(array $condition = []): Collection
    {
        return SimpleSlider::query()->where($condition)->get();
    }
}
