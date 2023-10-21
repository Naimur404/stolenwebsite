<?php

namespace Botble\Backup\Supports\PgSql;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Throwable;

class Restore
{
    public function restore(string $file): bool
    {
        $config = DB::getConfig();

        $pgRestorePath = rtrim(config('plugins.backup.general.pgsql.execute_path'), '/');

        $command = 'PGPASSWORD="%s" %s --username="%s" --host="%s" --port="%s" --dbname="%s" "%s"';

        $sql = sprintf(
            $command,
            $config['password'],
            $pgRestorePath . 'pg_restore',
            $config['username'],
            $config['host'],
            $config['port'],
            $config['database'],
            $file
        );

        try {
            DB::getSchemaBuilder()->dropAllTables();
            Process::fromShellCommandline($sql)->mustRun();
        } catch (Throwable) {
            try {
                system($sql);
            } catch (Throwable $exception) {
                throw $exception;
            }
        }

        return false;
    }
}
