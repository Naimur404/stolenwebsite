<?php

namespace Botble\Location\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Location\Exports\CsvLocationExport;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Maatwebsite\Excel\Excel;

class ExportController extends BaseController
{
    public function index()
    {
        PageTitle::setTitle(trans('plugins/location::location.export_location'));

        Assets::addScriptsDirectly(['vendor/core/plugins/location/js/export.js']);

        $countryCount = Country::query()->count();
        $stateCount = State::query()->count();
        $cityCount = City::query()->count();

        return view('plugins/location::export.index', compact('countryCount', 'stateCount', 'cityCount'));
    }

    public function export(CsvLocationExport $csvLocationExport)
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        return $csvLocationExport->download('exported_location.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
    }
}
