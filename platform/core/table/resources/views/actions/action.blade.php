@php /** @var \Botble\Table\Actions\Action $action */ @endphp

<a
    href="{{ $action->hasUrl() ? $action->getUrl() : 'javascript:void(0);' }}"
    @if (! $action->getAttribute('class'))
        @class([
            'btn',
            'btn-sm',
            'btn-icon' => $action->hasIcon(),
            $action->getColor(),
        ])
    @endif
    data-bs-toggle="tooltip"
    data-bs-original-title="{{ $action->getLabel() }}"
    @if($action->isAction())
        data-dt-single-action
        data-method="{{ $action->getActionMethod() }}"
        @if($action->isConfirmation())
            data-confirmation-modal="{{ $action->isConfirmation() ? 'true' : 'false' }}"
            data-confirmation-modal-title="{{ $action->getConfirmationModalTitle() }}"
            data-confirmation-modal-message="{{ $action->getConfirmationModalMessage() }}"
            data-confirmation-modal-button="{{ $action->getConfirmationModalButton() }}"
            data-confirmation-modal-cancel-button="{{ $action->getConfirmationModalCancelButton() }}"
        @endif
    @elseif($action->shouldOpenUrlInNewTable())
        target="_blank"
    @endif
    {!! $action->getAttributes() !!}
>
    @if($action->hasIcon())
        @if($action->isRenderabeIcon())
            {!! BaseHelper::clean($action->getIcon()) !!}
        @else
            <i class="{{ $action->getIcon() }}"></i>
        @endif
    @endif

    <span @class(['sr-only' => $action->hasIcon()])>{{ $action->getLabel() }}</span>
</a>
