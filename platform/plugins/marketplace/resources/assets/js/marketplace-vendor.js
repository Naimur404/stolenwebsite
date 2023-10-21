class MarketplaceVendorManagement {
    init() {
        $(document).on('click', '.approve-vendor-for-selling-button', event => {
            event.preventDefault();
            $('#confirm-approve-vendor-for-selling-button').data('action', $(event.currentTarget).prop('href'));
            $('#approve-vendor-for-selling-modal').modal('show');
        });

        $(document).on('click', '#confirm-approve-vendor-for-selling-button', event => {
            event.preventDefault();
            let _self = $(event.currentTarget);

            _self.addClass('button-loading');

            $.ajax({
                type: 'POST',
                cache: false,
                url: _self.data('action'),
                success: res => {
                    if (!res.error) {
                        Botble.showSuccess(res.message);
                        window.location.href = route('marketplace.unverified-vendors.index');
                    } else {
                        Botble.showError(res.message);
                    }
                    _self.removeClass('button-loading');
                    _self.closest('.modal').modal('hide');
                },
                error: res => {
                    Botble.handleError(res);
                    _self.removeClass('button-loading');
                }
            });
        });
    }
}

$(document).ready(() => {
    new MarketplaceVendorManagement().init();
});
