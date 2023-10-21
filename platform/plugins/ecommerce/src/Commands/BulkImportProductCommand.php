<?php

namespace Botble\Ecommerce\Commands;

use Botble\Ecommerce\Http\Requests\BulkImportRequest;
use Botble\Ecommerce\Http\Requests\ProductRequest;
use Botble\Ecommerce\Imports\ProductImport;
use Botble\Ecommerce\Imports\ValidateProductImport;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mimey\MimeTypes;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand('cms:ecommerce:products:import', 'Bulk import products command')]
class BulkImportProductCommand extends Command
{
    protected bool $deleteFileAfter = false;
    protected string $path;

    public function __construct(protected ProductImport $productImport, protected ValidateProductImport $validateProductImport)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $file = $this->getFile($this->argument('file'));
        $type = $this->option('type');

        if (! $file instanceof UploadedFile) {
            $this->error(Arr::get($file, 'message', 'File could not be processed, please check again!'));

            return self::FAILURE;
        }

        $rules = (new BulkImportRequest())->rules();
        $validator = Validator::make([
            'file' => $file,
            'type' => $type,
        ], $rules);

        if ($validator->fails()) {
            $this->warn('There is an error in the input data, please check again!');

            $this->table(
                ['Attribute', 'Errors'],
                collect($validator->errors())
                    ->map(fn ($item, $key) => [
                        'attribute' => $key,
                        'errors' => implode(', ', $item),
                    ])
                    ->toArray(),
            );
            $this->deleteFile();

            return self::FAILURE;
        }

        $this->newLine();
        $this->comment('Starting to check the information of each column data');

        $this->validateProductImport
            ->setValidatorClass(new ProductRequest())
            ->import($file);

        if ($this->validateProductImport->failures()->count()) {
            $this->warn(trans('plugins/ecommerce::bulk-import.import_failed_description'));
            $this->table(
                ['#Row', 'Attribute', 'Errors'],
                $this->validateProductImport
                    ->failures()
                    ->map(function ($item) {
                        return [
                            'row' => $item->row(),
                            'attribute' => $item->attribute(),
                            'errors' => implode(', ', $item->errors()),
                        ];
                    })
                    ->toArray(),
            );
            $this->deleteFile();

            return self::FAILURE;
        }

        $this->newLine();
        $this->comment('Starting to import data into the database');

        $this->productImport
            ->setValidatorClass(new ProductRequest())
            ->setImportType($type)
            ->import($file);

        $message = trans('plugins/ecommerce::bulk-import.imported_successfully');
        $result = trans('plugins/ecommerce::bulk-import.results', [
            'success' => $this->productImport->successes()->count(),
            'failed' => $this->productImport->failures()->count(),
        ]);

        $this->newLine();
        $this->info($message . ' ' . $result);
        $this->newLine();

        $this->deleteFile();

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Relative path or url to csv/excel file');
        $this->addOption('type', 'T', InputArgument::OPTIONAL, 'type of products', 'all');
    }

    protected function getFile(string $pathToFile): UploadedFile|array|null
    {
        $info = pathinfo($pathToFile);

        if (filter_var($pathToFile, FILTER_VALIDATE_URL)) {
            $this->comment('The path to the file is url, so it will be downloaded');

            try {
                $contents = file_get_contents($pathToFile);
                $this->info('File downloaded successfully');
            } catch (Exception) {
                return [
                    'error' => true,
                    'message' => 'Cannot get contents in file',
                ];
            }

            if (empty($contents)) {
                return [
                    'error' => true,
                    'message' => 'Contents in file is empty',
                ];
            }

            $path = storage_path('app/tmp');
            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0755);
            }

            $this->path = $path . '/' . $info['basename'] . '-' . Str::random(5);
            file_put_contents($this->path, $contents);
            $this->deleteFileAfter = true;
        } else {
            $this->path = base_path($pathToFile);
            if (! File::exists($this->path)) {
                return [
                    'error' => true,
                    'message' => 'File not found',
                ];
            }
        }
        $this->info('File path at: ' . $this->path);

        $mimeType = (new MimeTypes())->getMimeType(File::extension($pathToFile));
        $this->info('Mime type of file: "' . $mimeType . '"');

        return new UploadedFile($this->path, $info['basename'], $mimeType, null, true);
    }

    protected function deleteFile()
    {
        if ($this->deleteFileAfter && $this->path) {
            File::delete($this->path);
        }
    }
}
