<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('countries_backup');
        Schema::dropIfExists('states_backup');
        Schema::dropIfExists('cities_backup');
        Schema::dropIfExists('language_meta_backup');
    }
};
