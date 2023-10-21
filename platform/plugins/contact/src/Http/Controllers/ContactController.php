<?php

namespace Botble\Contact\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Contact\Enums\ContactStatusEnum;
use Botble\Contact\Forms\ContactForm;
use Botble\Contact\Http\Requests\ContactReplyRequest;
use Botble\Contact\Http\Requests\EditContactRequest;
use Botble\Contact\Models\Contact;
use Botble\Contact\Models\ContactReply;
use Botble\Contact\Tables\ContactTable;
use Exception;
use Illuminate\Http\Request;

class ContactController extends BaseController
{
    public function index(ContactTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/contact::contact.menu'));

        return $dataTable->renderTable();
    }

    public function edit(Contact $contact, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/contact::contact.edit'));

        return $formBuilder->create(ContactForm::class, ['model' => $contact])->renderForm();
    }

    public function update(Contact $contact, EditContactRequest $request, BaseHttpResponse $response)
    {
        $contact->fill($request->input());
        $contact->save();

        event(new UpdatedContentEvent(CONTACT_MODULE_SCREEN_NAME, $request, $contact));

        return $response
            ->setPreviousUrl(route('contacts.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Contact $contact, Request $request, BaseHttpResponse $response)
    {
        try {
            $contact->delete();
            event(new DeletedContentEvent(CONTACT_MODULE_SCREEN_NAME, $request, $contact));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function postReply(int|string $id, ContactReplyRequest $request, BaseHttpResponse $response)
    {
        $contact = Contact::query()->findOrFail($id);

        $message = BaseHelper::clean($request->input('message'));

        if (! $message) {
            return $response
                ->setError()
                ->setCode(422)
                ->setMessage(trans('validation.required', ['attribute' => 'message']));
        }

        EmailHandler::send($message, 'Re: ' . $contact->subject, $contact->email);

        ContactReply::query()->create([
            'message' => $message,
            'contact_id' => $contact->getKey(),
        ]);

        $contact->status = ContactStatusEnum::READ();
        $contact->save();

        return $response
            ->setMessage(trans('plugins/contact::contact.message_sent_success'));
    }
}
