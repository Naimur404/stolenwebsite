<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Exports\TemplateShippingRuleItemExport;
use Botble\Ecommerce\Forms\ShippingRuleItemForm;
use Botble\Ecommerce\Http\Requests\ShippingRuleItemImportRequest;
use Botble\Ecommerce\Http\Requests\ShippingRuleItemRequest;
use Botble\Ecommerce\Imports\ShippingRuleItemImport;
use Botble\Ecommerce\Imports\ValidateShippingRuleItemImport;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;
use Botble\Ecommerce\Tables\ShippingRuleItemTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;

class ShippingRuleItemController extends BaseController
{
    public function __construct(
        protected ShippingRuleItemImport $itemImport,
        protected ValidateShippingRuleItemImport $validateItemImport
    ) {
    }

    public function index(ShippingRuleItemTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::shipping.rule.item.name'));

        return $dataTable->renderTable();
    }

    public function create(Request $request, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        if ($request->ajax() && ($shippingRuleId = $request->input('shipping_rule_id'))) {
            ShippingRule::query()->findOrFail($shippingRuleId);

            $html = $formBuilder->create(ShippingRuleItemForm::class)
                ->setFormOption('template', 'core/base::forms.form-content-only')
                ->renderForm();

            return $response->setData(['html' => $html])
                ->setMessage(trans('plugins/ecommerce::shipping.rule.item.create'));
        }

        PageTitle::setTitle(trans('plugins/ecommerce::shipping.rule.item.create'));

        return $formBuilder->create(ShippingRuleItemForm::class)->renderForm();
    }

    public function store(ShippingRuleItemRequest $request, BaseHttpResponse $response)
    {
        $ruleId = $request->input('shipping_rule_id');
        $rule = ShippingRule::query()->findOrFail($ruleId);
        $request->merge([
            'country' => $rule->shipping->country,
        ]);

        $item = ShippingRuleItem::query()->create($request->input());

        event(new CreatedContentEvent(SHIPPING_RULE_ITEM_MODULE_SCREEN_NAME, $request, $item));

        $hasOperations = Auth::user()->hasAnyPermission(['ecommerce.shipping-rule-items.edit', 'ecommerce.shipping-rule-items.destroy']);

        return $response
            ->setPreviousUrl(route('ecommerce.shipping-rule-items.index'))
            ->setNextUrl(route('ecommerce.shipping-rule-items.edit', $item->id))
            ->setData([
                'id' => $item->id,
                'shipping_rule_id' => $rule->id,
                'html' => view('plugins/ecommerce::shipping.items.table-item', compact('item', 'hasOperations'))->render(),
            ])
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request, BaseHttpResponse $response)
    {
        $item = ShippingRuleItem::query()->findOrFail($id);

        event(new BeforeEditContentEvent($request, $item));
        $title = trans('plugins/ecommerce::shipping.rule.item.edit') . $item->name_item;

        if ($request->ajax()) {
            $html = $formBuilder->create(ShippingRuleItemForm::class, ['model' => $item])
                ->setFormOption('template', 'core/base::forms.form-content-only')
                ->renderForm();

            return $response->setData(compact('html'))->setMessage($title);
        }

        PageTitle::setTitle($title);

        return $formBuilder->create(ShippingRuleItemForm::class, ['model' => $item])->renderForm();
    }

    public function update(int|string $id, ShippingRuleItemRequest $request, BaseHttpResponse $response)
    {
        $item = ShippingRuleItem::query()->findOrFail($id);

        $request->merge([
            'country' => $item->shippingRule->shipping->country,
        ]);

        $item->fill($request->input());
        $item->save();

        event(new UpdatedContentEvent(SHIPPING_RULE_ITEM_MODULE_SCREEN_NAME, $request, $item));

        $hasOperations = Auth::user()->hasAnyPermission(['ecommerce.shipping-rule-items.edit', 'ecommerce.shipping-rule-items.destroy']);

        return $response
            ->setPreviousUrl(route('ecommerce.shipping-rule-items.index'))
            ->setData([
                'id' => $item->id,
                'shipping_rule_id' => $item->shipping_rule_id,
                'html' => view('plugins/ecommerce::shipping.items.table-item', compact('item', 'hasOperations'))->render(),
            ])
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $item = ShippingRuleItem::query()->findOrFail($id);

            $item->delete();

            event(new DeletedContentEvent(SHIPPING_RULE_ITEM_MODULE_SCREEN_NAME, $request, $item));

            return $response->setData([
                    'id' => $item->id,
                    'shipping_rule_id' => $item->shipping_rule_id,
                ])
                ->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function import()
    {
        PageTitle::setTitle(trans('plugins/ecommerce::shipping.rule.item.bulk-import.menu'));

        Assets::addScriptsDirectly(['vendor/core/plugins/ecommerce/js/bulk-import.js']);

        $template = new TemplateShippingRuleItemExport('xlsx');
        $headings = $template->headings();
        $data = $template->collection();
        $rules = $template->rules();

        return view('plugins/ecommerce::shipping.bulk-import.index', compact('data', 'headings', 'rules'));
    }

    public function postImport(ShippingRuleItemImportRequest $request, BaseHttpResponse $response)
    {
        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        $file = $request->file('file');

        $this->validateItemImport
            ->setValidatorClass(new ShippingRuleItemRequest())
            ->import($file);

        if ($this->validateItemImport->failures()->count()) {
            $data = [
                'total_failed' => $this->validateItemImport->failures()->count(),
                'total_error' => $this->validateItemImport->errors()->count(),
                'failures' => $this->validateItemImport->failures(),
            ];

            $message = trans('plugins/ecommerce::bulk-import.import_failed_description');

            return $response
                ->setError()
                ->setData($data)
                ->setMessage($message);
        }

        $this->itemImport
            ->setValidatorClass(new ShippingRuleItemRequest())
            ->setImportType($request->input('type'))
            ->import($file);

        $data = [
            'total_success' => $this->itemImport->successes()->count(),
            'total_failed' => $this->itemImport->failures()->count(),
            'total_error' => $this->itemImport->errors()->count(),
            'failures' => $this->itemImport->failures(),
            'successes' => $this->itemImport->successes(),
        ];

        $message = trans('plugins/ecommerce::bulk-import.imported_successfully');

        $result = trans('plugins/ecommerce::bulk-import.results', [
            'success' => $data['total_success'],
            'failed' => $data['total_failed'],
        ]);

        return $response->setData($data)->setMessage($message . ' ' . $result);
    }

    public function downloadTemplate(Request $request)
    {
        $extension = $request->input('extension');
        $extension = $extension == 'csv' ? $extension : Excel::XLSX;
        $writeType = $extension == 'csv' ? Excel::CSV : Excel::XLSX;
        $contentType = $extension == 'csv' ? ['Content-Type' => 'text/csv'] : ['Content-Type' => 'text/xlsx'];
        $fileName = 'template_shipping_rule_items_import.' . $extension;

        return (new TemplateShippingRuleItemExport($extension))->download($fileName, $writeType, $contentType);
    }

    public function items($ruleId, Request $request, BaseHttpResponse $response)
    {
        $rule = ShippingRule::query()->findOrFail($ruleId);

        $orderBy = $request->input('order_by');
        $orderDir = $request->input('order_dir');
        if ($orderBy) {
            $fillable = array_merge(ShippingRuleItem::query()->getModel()->getFillable(), ['id', 'created_at', 'updated_at']);
            if (in_array($orderBy, $fillable)) {
                if ($orderDir != 'DESC') {
                    $orderDir = 'ASC';
                }
            } else {
                $orderBy = '';
            }
        }
        $perPage = (int) $request->input('per_page', 12);
        $items = ShippingRuleItem::query()
            ->where('shipping_rule_id', $ruleId);

        if ($orderBy) {
            $items = $items->orderBy($orderBy, $orderDir);
        }
        if (! in_array($orderBy, ['created_at', 'id'])) {
            $items = $items
                ->orderByDesc('created_at')
                ->orderBy('id', 'desc');
        }

        $items = $items->paginate($perPage ?: 12);

        $html = view('plugins/ecommerce::shipping.items.table', compact('items', 'rule') + ['total' => $items->total()])->render();

        return $response->setData(compact('html'));
    }
}
