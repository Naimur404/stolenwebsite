'use strict';

(function($) {
    $(document).on('change', '.product-quantity .select-return-item-qty', function(e) {
        const $this = $(e.currentTarget)
        const $option = $this.find(':selected')
        if ($option.length) {
            $option.closest('tr').find('.return-amount').html($option.data('amount'))
        }
    })
})(jQuery)
