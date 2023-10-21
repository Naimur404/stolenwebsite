<?php

namespace Botble\Table\Actions\Concerns;

use Botble\Base\Models\BaseModel;
use Closure;

trait HasUrl
{
    protected Closure|string $url;

    protected bool $openUrlInNewTab = false;

    /**
     * @param \Closure(\Botble\Base\Models\BaseModel $model): string|string $url
     */
    public function url(Closure|string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function hasUrl(): bool
    {
        return isset($this->url);
    }

    public function getUrl(): string|null
    {
        if (! $this->hasUrl()) {
            return null;
        }

        return $this->url instanceof Closure ? call_user_func($this->url, $this->getModel()) : $this->url;
    }

    public function openUrlInNewTable(bool $openUrlInNewTab = true): static
    {
        $this->openUrlInNewTab = $openUrlInNewTab;

        return $this;
    }

    public function shouldOpenUrlInNewTable(): bool
    {
        return $this->openUrlInNewTab;
    }

    public function route(string $route, array $parameters = [], bool $absolute = true): static
    {
        $this
            ->url(fn (BaseModel $model) => route($route, array_merge($parameters, [$model->getKey()]), $absolute))
            ->permission($route);

        return $this;
    }
}
