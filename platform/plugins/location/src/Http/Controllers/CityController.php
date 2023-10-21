<?php

namespace Botble\Location\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Location\Forms\CityForm;
use Botble\Location\Http\Requests\CityRequest;
use Botble\Location\Http\Resources\CityResource;
use Botble\Location\Models\City;
use Botble\Location\Tables\CityTable;
use Exception;
use Illuminate\Http\Request;

class CityController extends BaseController
{
    public function index(CityTable $table)
    {
        PageTitle::setTitle(trans('plugins/location::city.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/location::city.create'));

        return $formBuilder->create(CityForm::class)->renderForm();
    }

    public function store(CityRequest $request, BaseHttpResponse $response)
    {
        $city = City::query()->create($request->input());

        event(new CreatedContentEvent(CITY_MODULE_SCREEN_NAME, $request, $city));

        return $response
            ->setPreviousUrl(route('city.index'))
            ->setNextUrl(route('city.edit', $city->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(City $city, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $city->name]));

        return $formBuilder->create(CityForm::class, ['model' => $city])->renderForm();
    }

    public function update(City $city, CityRequest $request, BaseHttpResponse $response)
    {
        $city->fill($request->input());
        $city->save();

        event(new UpdatedContentEvent(CITY_MODULE_SCREEN_NAME, $request, $city));

        return $response
            ->setPreviousUrl(route('city.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(City $city, Request $request, BaseHttpResponse $response)
    {
        try {
            $city->delete();

            event(new DeletedContentEvent(CITY_MODULE_SCREEN_NAME, $request, $city));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getList(Request $request, BaseHttpResponse $response)
    {
        $keyword = BaseHelper::stringify($request->input('q'));

        if (! $keyword) {
            return $response->setData([]);
        }

        $data = City::query()
            ->where('name', 'LIKE', '%' . $keyword . '%')
            ->select(['id', 'name'])
            ->take(10)
            ->orderBy('order')
            ->orderBy('name', 'ASC')
            ->get();

        $data->prepend(new City(['id' => 0, 'name' => trans('plugins/location::city.select_city')]));

        return $response->setData(CityResource::collection($data));
    }

    public function ajaxGetCities(Request $request, BaseHttpResponse $response)
    {
        $data = City::query()
            ->select(['id', 'name'])
            ->wherePublished()
            ->orderBy('order')
            ->orderBy('name', 'ASC');

        $stateId = $request->input('state_id');

        if ($stateId && $stateId != 'null') {
            $data = $data->where('state_id', $stateId);
        }

        $keyword = BaseHelper::stringify($request->query('k'));

        if ($keyword) {
            $data = $data
                ->where('name', 'LIKE', '%' . $keyword . '%')
                ->paginate(10);
        } else {
            $data = $data->get();
        }

        if ($keyword) {
            return $response->setData([CityResource::collection($data), 'total' => $data->total()]);
        }

        $data->prepend(new City(['id' => 0, 'name' => trans('plugins/location::city.select_city')]));

        return $response->setData(CityResource::collection($data));
    }
}
