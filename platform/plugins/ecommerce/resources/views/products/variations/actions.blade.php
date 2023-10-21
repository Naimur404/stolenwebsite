@if ($update)
    <a href="#" class="btn btn-info btn-trigger-edit-product-version"
        data-target="{{ $update }}"
        data-load-form="{{ $loadForm }}"
        data-bs-toggle="tooltip"
        title="{{ trans('plugins/ecommerce::products.edit_variation_item') }}">
        <i class="fa fa-edit"></i>
    </a>
@endif
@if ($delete)
    <a href="#" data-target="{{ $delete }}"
        data-id="{{ $item->id }}"
        title="{{ trans('plugins/ecommerce::products.delete') }}"
        data-bs-toggle="tooltip"
        class="btn-trigger-delete-version btn btn-danger">
        <i class="fa fa-trash"></i>
    </a>
@endif
