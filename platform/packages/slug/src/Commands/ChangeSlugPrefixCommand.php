<?php

namespace Botble\Slug\Commands;

use Botble\Slug\Models\Slug;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('cms:slug:prefix', 'Change/set prefix for slugs')]
class ChangeSlugPrefixCommand extends Command implements PromptsForMissingInput
{
    public function handle(): int
    {
        $data = Slug::query()
            ->where('reference_type', $this->argument('class'))
            ->update(['prefix' => $this->option('prefix') ?? '']);

        $this->components->info(sprintf('Processed %s item(s)!', number_format($data)));

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('class', InputArgument::REQUIRED, 'The model class');
        $this->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'The prefix of slugs');
    }
}
