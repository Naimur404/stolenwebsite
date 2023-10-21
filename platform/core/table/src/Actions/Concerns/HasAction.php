<?php

namespace Botble\Table\Actions\Concerns;

trait HasAction
{
    protected bool $isAction = false;

    protected string $actionMethod = 'POST';

    public function action(string $method = 'POST'): static
    {
        $this->isAction = true;
        $this->actionMethod = $method;

        return $this;
    }

    public function isAction(): bool
    {
        return $this->isAction;
    }

    public function getActionMethod(): string
    {
        return $this->actionMethod;
    }
}
