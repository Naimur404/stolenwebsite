<?php

namespace Botble\Newsletter\Contracts;

interface Factory
{
    public function driver(string $driver);
}
