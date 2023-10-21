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
use Botble\Location\Forms\CountryForm;
use Botble\Location\Http\Requests\CountryRequest;
use Botble\Location\Http\Resources\CountryResource;
use Botble\Location\Models\Country;
use Botble\Location\Tables\CountryTable;
use Exception;
use Illuminate\Http\Request;

class CountryController extends BaseController
{
    public function index(CountryTable $table)
    {
        PageTitle::setTitle(trans('plugins/location::country.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/location::country.create'));

        return $formBuilder->create(CountryForm::class)->renderForm();
    }

    public function store(CountryRequest $request, BaseHttpResponse $response)
    {
        $country = Country::query()->create($request->input());

        event(new CreatedContentEvent(COUNTRY_MODULE_SCREEN_NAME, $request, $country));

        return $response
            ->setPreviousUrl(route('country.index'))
            ->setNextUrl(route('country.edit', $country->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Country $country, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $country->name]));

        return $formBuilder->create(CountryForm::class, ['model' => $country])->renderForm();
    }

    public function update(Country $country, CountryRequest $request, BaseHttpResponse $response)
    {
        $country->fill($request->input());
        $country->save();

        event(new UpdatedContentEvent(COUNTRY_MODULE_SCREEN_NAME, $request, $country));

        return $response
            ->setPreviousUrl(route('country.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Country $country, Request $request, BaseHttpResponse $response)
    {
        try {
            $country->delete();

            event(new DeletedContentEvent(COUNTRY_MODULE_SCREEN_NAME, $request, $country));

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

        $data = Country::query()
            ->where('name', 'LIKE', '%' . $keyword . '%')
            ->select(['id', 'name'])
            ->take(10)
            ->orderBy('order')
            ->orderBy('name', 'ASC')
            ->get();

        $data->prepend(new Country(['id' => 0, 'name' => trans('plugins/location::city.select_country')]));

        return $response->setData(CountryResource::collection($data));
    }
}
