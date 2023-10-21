<?php

namespace Botble\Base\Supports;

use Illuminate\Support\Facades\DB;

class Database
{
    public static function restoreFromPath(string $pathToSqlFile, string $connection = null): void
    {
        DB::purge($connection);
        DB::connection()->setDatabaseName(DB::getDatabaseName());
        DB::getSchemaBuilder()->dropAllTables();
        DB::unprepared(file_get_contents($pathToSqlFile));
    }
}
