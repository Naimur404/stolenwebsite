<?php

namespace Botble\Table\Abstracts;

use Botble\Base\Contracts\BaseModel;
use Botble\Table\Abstracts\Concerns\HasConfirmation;
use Botble\Table\Abstracts\Concerns\HasLabel;
use Botble\Table\Abstracts\Concerns\HasPermissions;
use Botble\Table\Abstracts\Concerns\HasPriority;
use Illuminate\Contracts\Support\Htmlable;
use Stringable;

abstract class TableActionAbstract implements Htmlable, Stringable
{
    use HasConfirmation;
    use HasLabel;
    use HasPermissions;
    use HasPriority;

    protected BaseModel $model;

    public function __construct(protected string $name)
    {
    }

    public static function make(string $name): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function model(BaseModel $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): BaseModel
    {
        return $this->model;
    }

    abstract public function render();

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
