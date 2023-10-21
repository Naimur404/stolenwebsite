<?php

namespace Botble\AuditLog\Http\Controllers;

use Botble\AuditLog\Models\AuditHistory;
use Botble\AuditLog\Tables\AuditLogTable;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Exception;
use Illuminate\Http\Request;

class AuditLogController extends BaseController
{
    public function getWidgetActivities(BaseHttpResponse $response, Request $request)
    {
        $limit = $request->integer('paginate', 10);
        $limit = $limit > 0 ? $limit : 10;

        $histories = AuditHistory::query()
            ->with(['user'])
            ->orderByDesc('created_at')
            ->paginate($limit);

        return $response
            ->setData(view('plugins/audit-log::widgets.activities', compact('histories', 'limit'))->render());
    }

    public function index(AuditLogTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/audit-log::history.name'));

        return $dataTable->renderTable();
    }

    public function destroy(AuditHistory $log, Request $request, BaseHttpResponse $response)
    {
        try {
            $log->delete();

            event(new DeletedContentEvent(AUDIT_LOG_MODULE_SCREEN_NAME, $request, $log));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $ex) {
            return $response
                ->setError()
                ->setMessage($ex->getMessage());
        }
    }

    public function deleteAll(BaseHttpResponse $response)
    {
        AuditHistory::query()->truncate();

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
