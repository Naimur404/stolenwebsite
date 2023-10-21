<?php

namespace Botble\Shippo\Providers;

use Botble\Shippo\Commands\InitShippoCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InitShippoCommand::class,
        ]);
    }
}
