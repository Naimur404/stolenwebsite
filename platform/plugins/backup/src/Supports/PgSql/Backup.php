<?php

namespace Botble\Backup\Supports\PgSql;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Throwable;

class Backup
{
    public function backup(string $path): string
    {
        $config = DB::getConfig();

        $pgDumpPath = rtrim(config('plugins.backup.general.pgsql.execute_path'), '/');

        $command = 'PGPASSWORD="%s" %s --username="%s" --host="%s" --port="%s" --dbname="%s" -Fc > "%s"';

        $sql = sprintf(
            $command,
            $config['password'],
            $pgDumpPath . 'pg_dump',
            $config['username'],
            $config['host'],
            $config['port'],
            $config['database'],
            $filePath = $path . '.dump'
        );

        try {
            Process::fromShellCommandline($sql)->mustRun();
        } catch (Throwable) {
            try {
                system($sql);
            } catch (Throwable $exception) {
                throw $exception;
            }
        }

        return $filePath;
    }
}
