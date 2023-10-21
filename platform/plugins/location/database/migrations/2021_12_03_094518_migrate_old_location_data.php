<?php

use Botble\Language\Facades\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (is_plugin_active('language')) {
            Schema::dropIfExists('countries_backup');
            Schema::dropIfExists('states_backup');
            Schema::dropIfExists('cities_backup');

            DB::statement('CREATE TABLE countries_backup AS SELECT * FROM countries');
            DB::statement('CREATE TABLE states_backup AS SELECT * FROM states');
            DB::statement('CREATE TABLE cities_backup AS SELECT * FROM cities');

            $cities = LanguageMeta::query()->where('reference_type', State::class)
                ->where('lang_meta_code', '!=', Language::getDefaultLocaleCode())
                ->get();

            foreach ($cities as $item) {
                $originalItem = City::query()->find($item->reference_id);

                if (! $originalItem) {
                    continue;
                }

                $originalId = LanguageMeta::query()->where('lang_meta_origin', $item->lang_meta_origin)
                    ->where('lang_meta_code', Language::getDefaultLocaleCode())
                    ->where('reference_id', '!=', $originalItem->id)
                    ->value('reference_id');

                if (! $originalId) {
                    continue;
                }

                DB::table('cities_translations')->insertOrIgnore([
                    'cities_id' => $originalId,
                    'lang_code' => $item->lang_meta_code,
                    'name' => $originalItem->name,
                ]);

                if (is_plugin_active('real-estate')) {
                    DB::table('re_properties')->where('city_id', $originalItem->id)->update(['city_id' => $originalId]);
                }

                DB::table('cities')->where('id', $originalItem->id)->delete();
            }

            $states = LanguageMeta::query()->where('reference_type', State::class)
                ->where('lang_meta_code', '!=', Language::getDefaultLocaleCode())
                ->get();

            foreach ($states as $item) {
                $originalItem = State::query()->find($item->reference_id);

                if (! $originalItem) {
                    continue;
                }

                $originalId = LanguageMeta::query()->where('lang_meta_origin', $item->lang_meta_origin)
                    ->where('lang_meta_code', Language::getDefaultLocaleCode())
                    ->where('reference_id', '!=', $originalItem->id)
                    ->value('reference_id');

                if (! $originalId) {
                    continue;
                }

                DB::table('states_translations')->insertOrIgnore([
                    'states_id' => $originalId,
                    'lang_code' => $item->lang_meta_code,
                    'name' => $originalItem->name,
                    'abbreviation' => $originalItem->abbreviation,
                ]);

                City::query()->where('state_id', $originalItem->id)->update(['state_id' => $originalId]);

                DB::table('states')->where('id', $originalItem->id)->delete();
            }

            $countries = LanguageMeta::query()->where('reference_type', Country::class)
                ->where('lang_meta_code', '!=', Language::getDefaultLocaleCode())
                ->get();

            foreach ($countries as $item) {
                $originalItem = Country::query()->find($item->reference_id);

                if (! $originalItem) {
                    continue;
                }

                $originalId = LanguageMeta::query()->where('lang_meta_origin', $item->lang_meta_origin)
                    ->where('lang_meta_code', Language::getDefaultLocaleCode())
                    ->where('reference_id', '!=', $originalItem->id)
                    ->value('reference_id');

                if (! $originalId) {
                    continue;
                }

                DB::table('countries_translations')->insertOrIgnore([
                    'countries_id' => $originalId,
                    'lang_code' => $item->lang_meta_code,
                    'name' => $originalItem->name,
                    'nationality' => $originalItem->nationality,
                ]);

                City::query()->where('country_id', $originalItem->id)->update(['country_id' => $originalId]);
                State::query()->where('country_id', $originalItem->id)->update(['country_id' => $originalId]);

                DB::table('countries')->where('id', $originalItem->id)->delete();
            }

            DB::statement('CREATE TABLE language_meta_backup AS SELECT * FROM language_meta');

            DB::table('language_meta_backup')->insert(
                LanguageMeta::query()->where('reference_type', State::class)->get()->toArray()
            );
            DB::table('language_meta_backup')->insert(
                LanguageMeta::query()->where('reference_type', City::class)->get()->toArray()
            );
            DB::table('language_meta_backup')->insert(
                LanguageMeta::query()->where('reference_type', Country::class)->get()->toArray()
            );

            LanguageMeta::query()->where('reference_type', State::class)->delete();
            LanguageMeta::query()->where('reference_type', City::class)->delete();
            LanguageMeta::query()->where('reference_type', Country::class)->delete();

            Schema::dropIfExists('countries_backup');
            Schema::dropIfExists('states_backup');
            Schema::dropIfExists('cities_backup');
        }
    }
};
