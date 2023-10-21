<?php

namespace Botble\Location\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Helper;
use Botble\Location\Exports\TemplateLocationExport;
use Botble\Location\Http\Requests\BulkImportRequest;
use Botble\Location\Http\Requests\LocationImportRequest;
use Botble\Location\Imports\LocationImport;
use Botble\Location\Imports\ValidateLocationImport;
use Botble\Location\Location;
use Botble\Location\Models\Country;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;

class BulkImportController extends BaseController
{
    public function index()
    {
        PageTitle::setTitle(trans('plugins/location::bulk-import.name'));

        Assets::addScriptsDirectly(['vendor/core/plugins/location/js/bulk-import.js']);

        return view('plugins/location::bulk-import.index');
    }

    public function postImport(
        BulkImportRequest $request,
        BaseHttpResponse $response,
        LocationImport $locationImport,
        ValidateLocationImport $validateLocationImport
    ) {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        $file = $request->file('file');
        $importType = $request->input('type');

        $validateLocationImport
            ->setValidatorClass(new LocationImportRequest())
            ->setImportType($importType)
            ->import($file);

        if ($validateLocationImport->failures()->count()) {
            $data = [
                'total_failed' => $validateLocationImport->failures()->count(),
                'total_error' => $validateLocationImport->errors()->count(),
                'failures' => $validateLocationImport->failures(),
            ];

            return $response
                ->setError()
                ->setData($data)
                ->setMessage(trans('plugins/location::bulk-import.import_failed_description'));
        }

        $locationImport
            ->setValidatorClass(new LocationImportRequest())
            ->setImportType($importType)
            ->import($file);

        $data = [
            'total_success' => $locationImport->successes()->count(),
            'total_failed' => $locationImport->failures()->count(),
            'total_error' => $locationImport->errors()->count(),
            'failures' => $locationImport->failures(),
            'successes' => $locationImport->successes(),
        ];

        return $response->setData($data)->setMessage(
            trans('plugins/location::bulk-import.imported_successfully') . ' ' .
            trans('plugins/location::bulk-import.results', [
                'success' => $data['total_success'],
                'failed' => $data['total_failed'],
            ])
        );
    }

    public function downloadTemplate(Request $request)
    {
        $extension = $request->input('extension');
        $extension = $extension === 'csv' ? $extension : Excel::XLSX;
        $writeType = $extension === 'csv' ? Excel::CSV : Excel::XLSX;
        $contentType = $extension === 'csv' ? ['Content-Type' => 'text/csv'] : ['Content-Type' => 'text/xlsx'];
        $fileName = 'template_locations_import.' . $extension;

        return (new TemplateLocationExport($extension))->download($fileName, $writeType, $contentType);
    }

    public function ajaxGetAvailableRemoteLocations(Location $location, BaseHttpResponse $response)
    {
        $remoteLocations = $location->getRemoteAvailableLocations();

        $availableLocations = Country::query()->pluck('code')->all();

        $listCountries = Helper::countries();

        $locations = [];

        foreach ($remoteLocations as $location) {
            $location = strtoupper($location);

            if (in_array($location, $availableLocations)) {
                continue;
            }

            foreach ($listCountries as $key => $country) {
                if ($location === strtoupper($key)) {
                    $locations[$location] = $country;
                }
            }
        }

        $locations = array_unique($locations);

        return $response
            ->setData(view('plugins/location::partials.available-remote-locations', compact('locations'))->render());
    }

    public function importLocationData(string $countryCode, Location $location, BaseHttpResponse $response)
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        $result = $location->downloadRemoteLocation($countryCode);

        return $response
            ->setError($result['error'])
            ->setMessage($result['message']);
    }
}
