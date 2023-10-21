<div class="tab-pane" id="tab_extended_info">
    <div class="form-group">
        <div class="form__content">
            <div class="row">

                <div class="col-12">
                    <div class="form-group">
                        <label for="background">{{ __('Background') }}</label>
                        {!! Form::customImage('background', old('background', $background)) !!}
                        {!! Form::error('background', $errors) !!}
                    </div>
                </div>
                @if (! MarketplaceHelper::hideStoreSocialLinks())
                    <div class="col-12 border p-3">
                        <div>
                            <h2 class="h4 text-primary">{{ __('Socials') }}</h2>
                        </div>
                        <div class="row">
                            @foreach ($availableSocials as $k => $name)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="socials_{{ $k }}">{{ $name }}</label>
                                        {!! Form::url('socials[' . $k . ']', old('socials.' . $k, Arr::get($socials, $k, '')), [
                                            'class' => 'form-control',
                                            'id' => 'socials_' . $k,
                                            'placeholder' => __('Enter link for :name', ['name' => $name]),
                                            'maxlength'=> 255,
                                        ]) !!}
                                        {!! Form::error('socials.' . $k, $errors) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
