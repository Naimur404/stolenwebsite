<x-core-setting::section
    :title="trans('plugins/faq::faq.settings.title')"
    :description="trans('plugins/faq::faq.settings.description')"
>
    <x-core-setting::checkbox
        name="enable_faq_schema"
        :label="trans('plugins/faq::faq.settings.enable_faq_schema')"
        :checked="setting('enable_faq_schema', false)"
        :helper-text="trans('plugins/faq::faq.settings.enable_faq_schema_description')"
    />
</x-core-setting::section>
