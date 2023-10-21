<?php

return [
    'mysql' => [
        'execute_path' => env('BACKUP_MYSQL_EXECUTE_PATH', ''),
    ],

    'pgsql' => [
        'execute_path' => env('BACKUP_PGSQL_EXECUTE_PATH', ''),
    ],
];
