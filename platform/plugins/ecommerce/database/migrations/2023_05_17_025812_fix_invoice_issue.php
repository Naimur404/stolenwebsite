<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class () extends Migration
{
    public function up(): void
    {
        $file = storage_path('app/templates/invoice.tpl');

        if (File::exists($file)) {
            File::delete($file);
        }
    }
};
