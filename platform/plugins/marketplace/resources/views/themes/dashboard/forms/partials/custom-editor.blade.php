@php

$editor = new \Botble\Base\Supports\Editor();
$editor->registerAssets();

$attributes = Arr::set($attributes, 'class', Arr::get($attributes, 'class', 'form-control') . ' editor-' .
setting('rich_editor', config('core.base.general.editor.primary')));

$attributes['id'] = $name;
$attributes['rows'] = 3;

@endphp

{!! Form::textarea($name, $value, $attributes) !!}

@push('scripts')
    <script>
        'use strict';

        var RV_MEDIA_URL = {
            base: '{{ url('') }}',
            filebrowserImageBrowseUrl: false,
            media_upload_from_editor: '{{ route('marketplace.vendor.upload-from-editor') }}'
        }

        function setImageValue(file) {
            $('.mce-btn.mce-open').parent().find('.mce-textbox').val(file);
        }
    </script>
    <iframe id="form_target" name="form_target" style="display:none"></iframe>
    <form id="tinymce_form" action="{{ route('marketplace.vendor.upload-from-editor') }}" target="form_target" method="post" enctype="multipart/form-data" style="width:0;height:0;overflow:hidden;display: none;">
        @csrf
        <input name="upload" id="upload_file" type="file" onchange="$('#tinymce_form').submit();this.value='';">
        <input type="hidden" value="tinymce" name="upload_type">
    </form>
@endpush
