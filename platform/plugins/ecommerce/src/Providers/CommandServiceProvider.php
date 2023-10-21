<?php

namespace Botble\Ecommerce\Providers;

use Botble\Ecommerce\Commands\BulkImportProductCommand;
use Botble\Ecommerce\Commands\SendAbandonedCartsEmailCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            SendAbandonedCartsEmailCommand::class,
            BulkImportProductCommand::class,
        ]);
    }
}
