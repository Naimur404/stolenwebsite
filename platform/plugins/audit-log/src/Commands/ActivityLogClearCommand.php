<?php

namespace Botble\AuditLog\Commands;

use Botble\AuditLog\Models\AuditHistory;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:activity-logs:clear', 'Clear all activity logs')]
class ActivityLogClearCommand extends Command
{
    public function handle(): int
    {
        $this->components->info('Processing...');

        $count = AuditHistory::query()->count();
        AuditHistory::query()->truncate();

        $this->components->info(sprintf('Done. Deleted %s records!', number_format($count)));

        return self::SUCCESS;
    }
}
