<link rel="stylesheet" href="{{ asset('vendor/core/core/media/libraries/dropzone/dropzone.css') }}">
<script src="{{ asset('vendor/core/core/media/libraries/dropzone/dropzone.js') }}"></script>
<style>
    .dropzone {
        border-radius: 5px;
        border: 1px dashed rgb(0, 135, 247);
    }
    .dropzone .dz-preview:not(.dz-processing) .dz-progress {
        display: none;
    }

    .dropzone .dz-message {
        margin : 50px 0;
    }

    .dropzone.dz-clickable * {
        cursor: move;
    }
</style>
<script>
    'use strict';
    Dropzone.autoDiscover = false;

    $(document).ready(function () {
        var dropzone = new Dropzone('#{{ $id }}-upload', {
            previewTemplate: document.querySelector('#preview-template').innerHTML,
            parallelUploads: 1,
            thumbnailHeight: 120,
            thumbnailWidth: 120,
            addRemoveLinks: true,
            filesizeBase: 1000,
            uploadMultiple: {{ setting('media_chunk_enabled') == '1' ? 'false' : 'true' }},
            chunking: {{ setting('media_chunk_enabled') == '1' ? 'true' : 'false' }},
            forceChunking: true, // forces chunking when file.size < chunkSize
            parallelChunkUploads: false, // allows chunks to be uploaded in parallel (this is independent of the parallelUploads option)
            chunkSize: {{ setting('media_chunk_size', config('core.media.media.chunk.chunk_size')) }}, // chunk size 1,000,000 bytes (~1MB)
            retryChunks: true, // retry chunks on failure
            retryChunksLimit: 3, // retry maximum of 3 times (default is 3)
            timeout: 0, // MB,
            maxFilesize: {{ MarketplaceHelper::maxFilesizeUploadByVendor() }}, // MB
            maxFiles: {{ MarketplaceHelper::maxProductImagesUploadByVendor() }}, // max files upload,
            paramName: 'file',
            acceptedFiles: 'image/*',
            url: '{{ route('marketplace.vendor.upload') }}',
            sending: function(file, xhr, formData) {
                formData.append('_token', '{{ csrf_token() }}');
            },
            thumbnail: function(file, dataUrl) {
                if (file.previewElement) {
                    file.previewElement.classList.remove('dz-file-preview');
                    var images = file.previewElement.querySelectorAll('[data-dz-thumbnail]');
                    for (var i = 0; i < images.length; i++) {
                        var thumbnailElement = images[i];
                        thumbnailElement.alt = file.name;
                        thumbnailElement.src = dataUrl;
                    }
                    setTimeout(function() { file.previewElement.classList.add('dz-image-preview'); }, 1);

                    if (file.url) {
                        $(file.previewElement).append('<input type="hidden" name="{{ $name }}[]" value="' + file.url + '" />');
                    }
                }
            },
            success: function (file, response) {
                if (response.error) {
                    Botble.showError(response.message);
                } else {
                    if ({{ setting('media_chunk_enabled') == '1' ? 'true' : 'false' }}) {
                        response = JSON.parse(file.xhr.response);
                    }
                }

                $(file.previewElement).append('<input type="hidden" name="{{ $name }}[]" value="' + response.data.url + '" />');

                $('.dz-sortable').sortable();
            },
            removedfile: function(file) {
                if (! confirm('{{ __('Do you want to delete this image?') }}'))  {
                    return false;
                }
                dropzone.options.maxFiles = dropzone.options.maxFiles + 1;
                $('.dz-message.needsclick').hide();
                if (dropzone.options.maxFiles === {{ MarketplaceHelper::maxProductImagesUploadByVendor() }}) {
                    $('.dz-message.needsclick').show();
                }

                return file.previewElement != null ? file.previewElement.parentNode.removeChild(file.previewElement) : void 0;
            }
        });

        @if ($values)
        var files = [];
        @foreach($values as $item)
        files.push({name: '{{ File::name($item) }}', size: '{{ Storage::exists($item) ? Storage::size($item) : 0 }}', url: '{{ $item }}', full_url: '{{ RvMedia::getImageUrl($item, 'thumb') }}'});
        @endforeach

        $.each(files, function(key, file) {
            dropzone.options.addedfile.call(dropzone, file);
            dropzone.options.thumbnail.call(dropzone, file, file.full_url);
            dropzone.options.maxFiles = dropzone.options.maxFiles - 1;
        });

        $('.dz-sortable').sortable();
        @endif
    });
</script>
