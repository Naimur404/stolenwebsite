<!-- Modal -->
<div class="modal fade" id="product-review-modal" tabindex="-1" aria-labelledby="product-review-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header position-absolute border-0 top-0 end-0">
                <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-dialog-scrollable">
                @include('plugins/ecommerce::themes.customers.product-reviews.form', ['product' => null])
            </div>
        </div>
    </div>
</div>
