<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Tables\InvoiceTable;
use Exception;
use Illuminate\Http\Request;

class InvoiceController extends BaseController
{
    public function index(InvoiceTable $table)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::invoice.name'));

        return $table->renderTable();
    }

    public function edit(Invoice $invoice, Request $request)
    {
        event(new BeforeEditContentEvent($request, $invoice));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $invoice->code]));

        return view('plugins/ecommerce::invoices.edit', compact('invoice'));
    }

    public function destroy(Invoice $invoice, Request $request, BaseHttpResponse $response)
    {
        try {
            $invoice->delete();

            event(new DeletedContentEvent(INVOICE_MODULE_SCREEN_NAME, $request, $invoice));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getGenerateInvoice(Invoice $invoice, Request $request)
    {
        if ($request->input('type') === 'print') {
            return InvoiceHelper::streamInvoice($invoice);
        }

        return InvoiceHelper::downloadInvoice($invoice);
    }

    public function generateInvoices(BaseHttpResponse $response)
    {
        $orders = Order::query()
            ->where('is_finished', true)
            ->doesntHave('invoice')
            ->get();

        foreach ($orders as $order) {
            InvoiceHelper::store($order);
        }

        return $response
            ->setNextUrl(route('ecommerce.invoice.index'))
            ->setMessage(trans('plugins/ecommerce::invoice.generate_success_message', ['count' => $orders->count()]));
    }
}
