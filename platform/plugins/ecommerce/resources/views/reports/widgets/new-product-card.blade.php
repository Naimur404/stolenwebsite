<div class="d-flex px-2 pt-3 position-relative">
    <div class="block-left d-flex me-1">
        <span class="align-self-center bg-white p-1">
            <i class="fas fa-database fa-2x m-2"></i>
        </span>
    </div>
    <div class="block-content mx-3">
        <p class="mb-1">{{ trans('plugins/ecommerce::reports.products') }}</p>
        <h5>{{ $count }}</h5>
    </div>
</div>

@include('plugins/ecommerce::reports.widgets.card-description')
