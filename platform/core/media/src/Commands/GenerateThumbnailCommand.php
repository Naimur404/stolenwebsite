<?php

namespace Botble\Media\Commands;

use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:media:thumbnail:generate', 'Generate thumbnails for images')]
class GenerateThumbnailCommand extends Command
{
    public function handle(): int
    {
        $this->components->info('Starting to generate thumbnails...');

        $files = MediaFile::query()->select(['url', 'mime_type', 'folder_id'])->get();

        $this->components->info(sprintf('Processing %s %s...', number_format($files->count()), Str::plural('file', $files->count())));

        $errors = [];

        foreach ($files as $file) {
            try {
                RvMedia::generateThumbnails($file);
            } catch (Exception $exception) {
                $errors[] = $file->url;
                $this->components->error($exception->getMessage());
            }
        }

        $this->components->info('Generated media thumbnails successfully!');

        $errors = array_unique($errors);

        $errors = array_map(function ($item) {
            return [$item];
        }, $errors);

        if ($errors) {
            $this->components->info('We are unable to regenerate thumbnail for these files:');

            $this->table(['File directory'], $errors);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
