<?php

namespace Botble\Table\Columns;

class LinkableColumn extends Column
{
    protected array $route = [];

    public function route(string $route, array $parameters = [], bool $absolute = true): static
    {
        $this->route = [$route, $parameters, $absolute];

        return $this;
    }

    public function getRoute(): array
    {
        return $this->route;
    }
}
