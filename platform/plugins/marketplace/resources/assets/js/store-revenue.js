$(() => {
    function submitFormAjax($form, $button) {
        $button.addClass('button-loading');

        $.ajax({
            type: 'POST',
            cache: false,
            url: $form.prop('action'),
            data: $form.serialize(),
            success: res => {
                if (!res.error) {
                    Botble.showNotice('success', res.message);
                    $button.closest('.modal').modal('hide');
                    if (window.LaravelDataTables) {
                        Object.keys(window.LaravelDataTables).map((x) => {
                            window.LaravelDataTables[x].draw();
                        });
                    }
                    if (res.data && res.data.balance) {
                        $('.vendor-balance').text(res.data.balance);
                    }
                } else {
                    Botble.showNotice('error', res.message);
                }
            },
            error: res => {
                Botble.handleError(res);
            },
            complete: () => {
                $button.removeClass('button-loading');
            }
        });
    }

    $(document).on('click', '#confirm-update-amount-button', event => {
        event.preventDefault();
        let _self = $(event.currentTarget);
        submitFormAjax($('#update-balance-modal .modal-body form'), _self);
    });

    $(document).on('submit', '#update-balance-modal .modal-body form', event => {
        event.preventDefault();
        let _self = $(event.currentTarget);
        submitFormAjax(_self, $('#confirm-update-amount-button'));
    });
});
