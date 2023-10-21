<?php

namespace Botble\Table\Http\Controllers;

use App\Http\Controllers\Controller;
use Botble\Base\Facades\Form;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Http\Requests\BulkChangeRequest;
use Botble\Table\Http\Requests\FilterRequest;
use Botble\Table\TableBuilder;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function __construct(protected TableBuilder $tableBuilder)
    {
    }

    public function getDataForBulkChanges(BulkChangeRequest $request): array
    {
        $class = $request->input('class');

        if (! $class || ! class_exists($class)) {
            return [];
        }

        $object = $this->tableBuilder->create($class);

        $data = $object->getValueInput(null, null, 'text');
        if (! $request->input('key')) {
            return $data;
        }

        $column = Arr::get($object->getAllBulkChanges(), $request->input('key'));
        if (empty($column)) {
            return $data;
        }

        $labelClass = 'control-label';
        if (Str::contains(Arr::get($column, 'validate'), 'required')) {
            $labelClass .= ' required';
        }

        $label = '';
        if (! empty($column['title'])) {
            $label = Form::label($column['title'], null, ['class' => $labelClass])->toHtml();
        }

        if (isset($column['callback']) && method_exists($object, $column['callback'])) {
            $data = $object->getValueInput(
                $column['title'],
                null,
                $column['type'],
                call_user_func([$object, $column['callback']])
            );
        } else {
            $data = $object->getValueInput($column['title'], null, $column['type'], Arr::get($column, 'choices', []));
        }

        $data['html'] = $label . $data['html'];

        return $data;
    }

    public function postSaveBulkChange(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/table::table.please_select_record'));
        }

        $inputKey = $request->input('key');
        $inputValue = $request->input('value');

        $class = $request->input('class');

        if (! $class || ! class_exists($class)) {
            return $response->setError();
        }

        $object = $this->tableBuilder->create($class);

        $columns = $object->getAllBulkChanges();

        if (! empty($columns[$inputKey]['validate'])) {
            $validator = Validator::make($request->input(), [
                'value' => $columns[$inputKey]['validate'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->messages()->first());
            }
        }

        try {
            $object->saveBulkChanges($ids, $inputKey, $inputValue);
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }

        return $response->setMessage(trans('core/table::table.save_bulk_change_success'));
    }

    public function postDispatchBulkAction(Request $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $request->validate([
            'bulk_action' => ['sometimes', 'required', 'boolean'],
            'bulk_action_table' => ['required_with:bulk_action', 'string'],
            'bulk_action_target' => ['required_with:bulk_action', 'string'],
            'ids' => ['required_with:bulk_action', 'array'],
            'ids.*' => ['required'],
        ]);

        if (
            ! class_exists($request->input('bulk_action_table')) ||
            ! class_exists($request->input('bulk_action_target'))
        ) {
            return $response
                ->setError()
                ->setMessage(trans('core/table::invalid_bulk_action'));
        }

        try {
            /**
             * @var TableAbstract $table
             */
            $table = app()->make($request->input('bulk_action_table'));

            abort_unless($table instanceof TableAbstract, 400);

            return $table->dispatchBulkAction();
        } catch (BindingResolutionException) {
            return $response
                ->setError()
                ->setMessage(__('Something went wrong.'));
        }
    }

    public function getFilterInput(FilterRequest $request)
    {
        $class = $request->input('class');

        if (! $class || ! class_exists($class)) {
            return [];
        }

        $object = $this->tableBuilder->create($class);

        $data = $object->getValueInput(null, null, 'text');
        if (! $request->input('key')) {
            return $data;
        }

        $column = Arr::get($object->getFilters(), $request->input('key'));
        if (empty($column)) {
            return $data;
        }

        $value = $request->input('value');
        $choices = Arr::get($column, 'choices', []);

        if (isset($column['callback']) && method_exists($object, $column['callback'])) {
            $choices = call_user_func_array([$object, $column['callback']], [$value]);
        }

        return $object->getValueInput(
            null,
            $value,
            $column['type'],
            $choices
        );
    }
}
