<?php

namespace Botble\Table\Actions;

use Botble\Table\Abstracts\TableActionAbstract;
use Botble\Table\Actions\Concerns\HasAction;
use Botble\Table\Actions\Concerns\HasAttributes;
use Botble\Table\Actions\Concerns\HasColor;
use Botble\Table\Actions\Concerns\HasIcon;
use Botble\Table\Actions\Concerns\HasUrl;

class Action extends TableActionAbstract
{
    use HasAction;
    use HasAttributes;
    use HasColor;
    use HasIcon;
    use HasUrl;

    public function render(): string
    {
        return view('core/table::actions.action', [
            'action' => $this,
        ])->render();
    }
}
