<?php

namespace Botble\Backup\Supports;

use Botble\Backup\Supports\MySql\MySqlDump;
use Botble\Backup\Supports\PgSql\Backup as PgSqlBackup;
use Botble\Backup\Supports\PgSql\Restore as PgSqlRestore;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Database;
use Botble\Base\Supports\Zipper;
use Carbon\Carbon;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class Backup
{
    protected string|null $folder = null;

    public function __construct(protected Filesystem $files, protected Zipper $zipper)
    {
    }

    public function createBackupFolder(string $name, string|null $description = null): array
    {
        $backupFolder = $this->createFolder($this->getBackupPath());
        $now = Carbon::now()->format('Y-m-d-H-i-s');
        $this->folder = $this->createFolder($backupFolder . DIRECTORY_SEPARATOR . $now);

        $file = $this->getBackupPath('backup.json');
        $data = [];

        if (file_exists($file)) {
            $data = BaseHelper::getFileData($file);
        }

        $data[$now] = [
            'name' => $name,
            'description' => $description,
            'date' => Carbon::now()->toDateTimeString(),
        ];

        BaseHelper::saveFileData($file, $data);

        return [
            'key' => $now,
            'data' => $data[$now],
        ];
    }

    public function createFolder(string $folder): string
    {
        $this->files->ensureDirectoryExists($folder);

        return $folder;
    }

    public function getBackupPath(string|null $path = null): string
    {
        return storage_path('app/backup') . ($path ? '/' . $path : null);
    }

    public function getBackupDatabasePath(string $key): string
    {
        return $this->getBackupPath($key . '/database-' . $key . '.zip');
    }

    public function isDatabaseBackupAvailable(string $key): bool
    {
        $file = $this->getBackupDatabasePath($key);

        return file_exists($file) && filesize($file) > 1024;
    }

    public function getBackupList(): array
    {
        $file = $this->getBackupPath('backup.json');
        if (file_exists($file)) {
            return BaseHelper::getFileData($file);
        }

        return [];
    }

    public function backupDb(): bool
    {
        $file = 'database-' . Carbon::now()->format('Y-m-d-H-i-s');
        $path = $this->folder . DIRECTORY_SEPARATOR . $file;

        if (! empty($mysqlPath)) {
            $mysqlPath = $mysqlPath . '/';
        }

        $driver = DB::getConfig('driver');

        if (! $driver) {
            return false;
        }

        try {
            switch ($driver) {
                case 'mysql':
                    return $this->backupDbMySql($path);
                case 'pgsql':
                    return $this->backupDbPgSql($path);
                default:
                    throw new RuntimeException(sprintf('Driver [%s] is not supported', $driver));
            }
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        return true;
    }

    protected function backupDbMySql(string $path): bool
    {
        $config = DB::getConfig();
        $mysqlPath = rtrim(config('plugins.backup.general.mysql.execute_path'), '/');
        $command = $mysqlPath . 'mysqldump --user="%s" --password="%s" --host="%s" --port="%s" "%s" > "%s"';
        $sql = sprintf(
            $command,
            $config['username'],
            $config['password'],
            $config['host'],
            $config['port'],
            $config['database'],
            $filePath = $path . '.sql'
        );

        try {
            Process::fromShellCommandline($sql)->mustRun();
        } catch (Exception) {
            try {
                if (function_exists('system')) {
                    system($sql);
                } else {
                    $this->processMySqlDumpPHP($path, $config);
                }
            } catch (Exception) {
                $this->processMySqlDumpPHP($path, $config);
            }
        }

        if (! $this->files->exists($filePath) || $this->files->size($filePath) < 1024) {
            $this->processMySqlDumpPHP($path, $config);
        }

        $this->compressFileToZip($filePath, $fileZip = $path . '.zip');

        if ($this->files->exists($fileZip)) {
            chmod($fileZip . '.zip', 0755);
        }

        return true;
    }

    protected function processMySqlDumpPHP(string $path, array $config): bool
    {
        $dump = new MySqlDump('mysql:host=' . $config['host'] . ';dbname=' . $config['database'], $config['username'], $config['password']);

        $dump->start($path . '.sql');

        return true;
    }

    protected function backupDbPgSql(string $path): bool
    {
        $file = (new PgSqlBackup())->backup($path);

        $this->compressFileToZip($file, $fileZip = $path . '.zip');

        if ($this->files->exists($fileZip)) {
            chmod($fileZip . '.zip', 0755);
        }

        return true;
    }

    public function compressFileToZip(string $path, string $destination): void
    {
        $this->zipper->compress($path, $destination);

        $this->deleteFile($path);
    }

    protected function deleteFile(string $file): void
    {
        if ($this->files->exists($file)) {
            $this->files->delete($file);
        }
    }

    public function backupFolder(string $source): bool
    {
        $file = $this->folder . DIRECTORY_SEPARATOR . 'storage-' . Carbon::now()->format('Y-m-d-H-i-s') . '.zip';

        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        if (! $this->zipper->compress($source, $file)) {
            $this->deleteFolderBackup($this->folder);
        }

        if (file_exists($file)) {
            chmod($file, 0755);
        }

        return true;
    }

    public function deleteFolderBackup(string $path): void
    {
        $backupFolder = $this->getBackupPath();

        if ($this->files->isDirectory($backupFolder) && $this->files->isDirectory($path)) {
            foreach (BaseHelper::scanFolder($path) as $item) {
                $this->files->delete($path . DIRECTORY_SEPARATOR . $item);
            }
            $this->files->deleteDirectory($path);

            if (empty($this->files->directories($backupFolder))) {
                $this->files->deleteDirectory($backupFolder);
            }
        }

        $file = $this->getBackupPath('backup.json');
        $data = [];

        if (file_exists($file)) {
            $data = BaseHelper::getFileData($file);
        }

        if (! empty($data)) {
            unset($data[Arr::last(explode('/', $path))]);
            BaseHelper::saveFileData($file, $data);
        }
    }

    public function restoreDatabase(string $file, string $path): bool
    {
        $driver = DB::getConfig('driver');

        if (! $driver) {
            return false;
        }

        $this->extractFileTo($file, $path);

        $file = $path . DIRECTORY_SEPARATOR . $this->files->name($file) . (
            $driver === 'mysql' ? '.sql' : '.dump'
        );

        if (! file_exists($file)) {
            return false;
        }

        try {
            switch ($driver) {
                case 'mysql':
                    return $this->restoreDbMySql($file);
                case 'pgsql':
                    return $this->restoreDbPgSql($file);
                default:
                    throw new RuntimeException(sprintf('Driver [%s] is not supported', $driver));
            }
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        $this->deleteFile($file);

        return true;
    }

    protected function restoreDbMySql(string $file): bool
    {
        Database::restoreFromPath($file);

        return true;
    }

    protected function restoreDbPgSql(string $file): bool
    {
        return (new PgSqlRestore())->restore($file);
    }

    public function extractFileTo(string $fileName, string $pathTo): bool
    {
        $this->zipper->extract($fileName, $pathTo);

        return true;
    }

    public function cleanDirectory(string $directory): bool
    {
        foreach ($this->files->glob(rtrim($directory, '/') . '/*') as $item) {
            if ($this->files->isDirectory($item)) {
                $this->files->deleteDirectory($item);
            } elseif (! in_array($this->files->basename($item), ['.htaccess', '.gitignore'])) {
                $this->files->delete($item);
            }
        }

        return true;
    }
}
