<?php

namespace Botble\Ecommerce\Option\Interfaces;

interface OptionTypeInterface
{
    public function render();

    public function view(): string;
}
