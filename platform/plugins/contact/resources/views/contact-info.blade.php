@if ($contact)
    <p>{{ trans('plugins/contact::contact.tables.time') }}: <i>{{ $contact->created_at }}</i></p>
    <p>{{ trans('plugins/contact::contact.tables.full_name') }}: <i>{{ $contact->name }}</i></p>
    <p>{{ trans('plugins/contact::contact.tables.email') }}: <i>{{ Html::mailto($contact->email) }}</i></p>
    <p>{{ trans('plugins/contact::contact.tables.phone') }}: <i>@if ($contact->phone) <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a> @else N/A @endif</i></p>
    <p>{{ trans('plugins/contact::contact.tables.address') }}: <i>{{ $contact->address ?: 'N/A' }}</i></p>
    <p>{{ trans('plugins/contact::contact.tables.subject') }}: <i>{{ $contact->subject ?: 'N/A' }}</i></p>
    <p>{{ trans('plugins/contact::contact.tables.content') }}:</p>
    <pre class="message-content">{{ $contact->content ?: '...' }}</pre>
@endif
