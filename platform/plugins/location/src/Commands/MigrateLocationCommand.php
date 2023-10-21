<?php

namespace Botble\Location\Commands;

use Botble\Location\Facades\Location;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('cms:location:migrate', 'Migrate location columns to table')]
class MigrateLocationCommand extends Command
{
    public function handle(): int
    {
        $className = str_replace('/', '\\', $this->option('class'));
        $error = true;

        if (! $className) {
            foreach (Location::supportedModels() as $className) {
                $this->runSchema($className);
                $error = false;
            }
        } elseif (Location::isSupported($className)) {
            $this->runSchema($className);
            $error = false;
        }

        if ($error) {
            $this->error('Not supported model');
        } else {
            $this->info('Migrate location successfully!');
        }

        return self::SUCCESS;
    }

    public function runSchema(string $className): void
    {
        $model = new $className();
        Schema::connection($model->getConnectionName())->table(
            $model->getTable(),
            function (Blueprint $table) use ($className) {
                $table->location($className);
            }
        );
    }

    protected function configure(): void
    {
        $this->addOption('class', null, InputOption::VALUE_REQUIRED, 'The model class name');
    }
}
