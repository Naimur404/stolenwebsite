<div class="container-xxxl">
    <div class="row py-5 mt-5">
        <div class="col-md-4">
            <div class="contact-page-info mx-3">
                <h2>{{ $shortcode->title }}</h2>
                <div class="fs-5 mt-5 mb-3">{{ $shortcode->subtitle }}</div>
                @for ($i = 1; $i <= 3; $i++)
                    @if ($shortcode->{'name_' . $i} && $shortcode->{'address_' . $i})
                        <div class="contact-page-info-item">
                            <small class="fw-bold text-uppercase">{{ $shortcode->{'name_' . $i} }}</small>
                            <div class="fs-5">
                                <p>{{ $shortcode->{'address_' . $i} }}</p>
                                @if ($phone = $shortcode->{'phone_' . $i})
                                    <p><a href="tel:{{ $phone }}">{{ $phone }}</a></p>
                                @endif
                                @if ($email = $shortcode->{'email_' . $i})
                                    <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                                @endif
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        </div>
        @if ($shortcode->show_contact_form && is_plugin_active('contact'))
            <div class="col-md-8 border-start">
                {!! '[contact-form][/contact-form]' !!}
            </div>
        @endif
    </div>
</div>
