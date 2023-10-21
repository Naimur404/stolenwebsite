<?php

namespace Botble\Newsletter\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Newsletter\Models\Newsletter;
use Botble\Newsletter\Tables\NewsletterTable;
use Exception;
use Illuminate\Http\Request;

class NewsletterController extends BaseController
{
    public function index(NewsletterTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/newsletter::newsletter.name'));

        return $dataTable->renderTable();
    }

    public function destroy(Newsletter $newsletter, Request $request, BaseHttpResponse $response)
    {
        try {
            $newsletter->delete();

            event(new DeletedContentEvent(NEWSLETTER_MODULE_SCREEN_NAME, $request, $newsletter));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
