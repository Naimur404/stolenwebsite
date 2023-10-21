<?php

namespace Botble\Location\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Location\Commands\MigrateLocationCommand;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            MigrateLocationCommand::class,
        ]);
    }
}
