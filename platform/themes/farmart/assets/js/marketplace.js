(function($) {
    'use strict'

    function handleToggleDrawer() {
        $('.ps-drawer-toggle').on('click', function() {
            $('.ps-drawer--mobile').addClass('active')
            $('.ps-site-overlay').addClass('active')
        })

        $('.ps-drawer__close').on('click', function() {
            $('.ps-drawer--mobile').removeClass('active')
            $('.ps-site-overlay').removeClass('active')
        })

        $('body').on('click', function(e) {
            if (
                $(e.target)
                    .siblings('.ps-drawer--mobile')
                    .hasClass('active')
            ) {
                $('.ps-drawer--mobile').removeClass('active')
                $('.ps-site-overlay').removeClass('active')
            }
        })
    }

    function tabs() {
        $('.ps-tab-list  li > a ').on('click', function(e) {
            e.preventDefault()
            const target = $(this).attr('href')
            $(this).closest('li').siblings('li').removeClass('active')
            $(this).closest('li').addClass('active')
            $(target).addClass('active')
            $(target).siblings('.ps-tab').removeClass('active')
        })
    }

    $(function() {
        tabs()
        handleToggleDrawer()
    })
})(jQuery)
