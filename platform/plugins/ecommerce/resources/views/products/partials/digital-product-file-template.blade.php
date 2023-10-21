<script type="text/x-custom-template" id="digital_attachment_template">
    <tr data-id="__id__">
        <td>
            <a class="text-danger remove-attachment-input"><i class="fas fa-minus-circle"></i></a>
        </td>
        <td>
            <i class="fas fa-paperclip"></i>
            <span class="d-inline-block ms-1">__file_name__</span>
        </td>
        <td>__file_size__</td>
        <td>-</td>
        <td class="text-end">
            <span class="text-warning">{{ trans('plugins/ecommerce::products.digital_attachments.unsaved') }}</span>
        </td>
    </tr>
</script>
<script type="text/x-custom-template" id="digital_attachment_external_template">
    <tr data-id="__id__">
        <td>
            <a class="text-danger remove-attachment-input"><i class="fas fa-minus-circle"></i></a>
        </td>
        <td>
            <input name="product_files_external[__id__][name]" class="form-control mb-1"
                placeholder="{{ trans('plugins/ecommerce::products.digital_attachments.enter_file_name') }}">
            <input type="url" name="product_files_external[__id__][link]" class="form-control" required
                placeholder="{{ trans('plugins/ecommerce::products.digital_attachments.enter_external_link_download') }} (*)">
        </td>
        <td colspan="2">
            <div class="input-group">
                <input type="number" name="product_files_external[__id__][size]" class="form-control"
                    placeholder="{{ trans('plugins/ecommerce::products.digital_attachments.enter_file_size') }}" value="0" min="0">
                <span class="input-group-text">
                    {!! Form::select('product_files_external[__id__][unit]', ['B' => 'B', 'kB' => 'kB', 'MB' => 'MB', 'GB' => 'GB', 'TB' => 'TB'], 'kB', ['class' => 'form-select form-select-sm bg-transparent border-0']) !!}
                </span>
            </div>
        </td>
        <td class="text-end">
            <span class="text-warning">{{ trans('plugins/ecommerce::products.digital_attachments.unsaved') }}</span>
        </td>
    </tr>
</script>
