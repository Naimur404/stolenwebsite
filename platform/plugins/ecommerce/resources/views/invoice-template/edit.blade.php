@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    {!! Form::open(['method' => 'PUT', 'route' => ['invoice-template.update']]) !!}
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>{{ trans('plugins/ecommerce::invoice-template.setting') }}</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">
                        {!! BaseHelper::clean(trans('plugins/ecommerce::invoice-template.setting_description')) !!}
                    </p>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20 email-template-edit-wrap">
                    <div class="form-group mb-3">
                        <label class="text-title-field" for="email_content">{{ trans('plugins/ecommerce::invoice-template.setting_content') }}</label>
                        <div class="d-inline-flex mb-3">
                            <div class="dropdown me-2">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-code"></i> {{ __('Variables') }}
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach($variables as $key => $label)
                                        <li>
                                            <a href="#" class="js-select-mail-variable" data-key="{{ $key }}">
                                                <span class="text-danger">{{ $key }}</span>: {{ trans($label) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-code"></i> {{ __('Functions') }}
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach(EmailHandler::getFunctions() as $key => $function)
                                        <li>
                                            <a href="#" class="js-select-mail-function" data-key="{{ $key }}" data-sample="{{ $function['sample'] }}">
                                                <span class="text-danger">{{ $key }}</span>: {{ trans($function['label']) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <textarea id="mail-template-editor" name="content" class="form-control" style="overflow-y:scroll; height: 500px;">{{ $content }}</textarea>
                        <p>
                            {{ Form::helper(__('Learn more about Twig template: :url', ['url' => Html::link('https://twig.symfony.com/doc/3.x/', null, ['target' => '_blank'])])) }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <div class="flexbox-annotated-section" style="border: none">
            <div class="flexbox-annotated-section-annotation">
                &nbsp
            </div>
            <div class="flexbox-annotated-section-content">
                <button type="button" class="btn btn-warning btn-trigger-reset-to-default" data-target="{{ route('invoice-template.reset') }}">
                    {{ trans('core/setting::setting.email.reset_to_default') }}
                </button>
                <a href="{{ route('invoice-template.preview') }}" target="_blank" class="btn btn-primary btn-trigger-preview-invoice-template">
                    {{ trans('plugins/ecommerce::invoice-template.preview') }}
                    <i class="fa fa-external-link"></i>
                </a>
                <button class="btn btn-info" type="submit" name="submit">
                    {{ trans('core/setting::setting.save_settings') }}
                </button>
            </div>
        </div>
    </div>
    {!! Form::close() !!}

    <x-core-base::modal
        id="reset-template-to-default-modal"
        :title="trans('core/setting::setting.email.confirm_reset')"
        button-id="reset-template-to-default-button"
        :button-label="trans('core/setting::setting.email.continue')"
    >
        {!! trans('core/setting::setting.email.confirm_message') !!}
    </x-core-base::modal>
@endsection
