<?php

use Botble\Base\Supports\Helper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('countries', 'code')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->string('nationality', 120)->nullable()->change();
            $table->string('code', 10)->nullable();
        });

        foreach (DB::table('countries')->get() as $country) {
            $country->code = Helper::getCountryCodeByName($country->name);
            $country->save();
        }
    }
};
