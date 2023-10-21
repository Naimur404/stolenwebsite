class MarketplaceSetting {
    constructor() {
        this.eventListeners()
    }

    eventListeners() {
        $(document).on('click', '#add-new-commission-setting-category', event => {
            event.preventDefault();
            event.stopPropagation();

            this.addNewCommissionSetting(event.currentTarget);

            return false;
        });

        $(document).on('click', '.remove-commission-setting', e => {
            e.preventDefault();
            e.stopPropagation();
            const index = $(e.target).attr('data-index')
            $(document).find(`#commission-setting-item-${index}`).remove();
        })

        let input = document.querySelectorAll('.tagify-commission-setting')
        input.forEach(element => {
            this.tagify(element);
        });
    }

    tagify(element) {
        const self = this;
        new Tagify(element, {
            delimiters: null,
            enforceWhitelist: true,
            whitelist: self.formatWhitelist(),
            dropdown: {
                enabled: 1, // suggest tags after a single character input
                classname: 'extra-properties', // custom class for the suggestion dropdown,
                searchBy: ['name']
            }
        })
    }

    formatWhitelist() {
        let data = [];
        window.tagifyWhitelist.map(item => {
            data.push({
                value: item.name,
                id: item.id
            })
        });

        return data;
    }

    addNewCommissionSetting() {
        const tpl = $('#commission-setting-item-template').html();
        const index = $('.commission-setting-item').length;
        let html = tpl.replace(/__index__/g, index)
        $('.commission-setting-item-wrapper').append(html);
        const element = document.querySelector(`#commission-setting-item-${index} .tagify-commission-setting`);
        this.tagify(element);
    }
}

$(document).ready(function () {
    new MarketplaceSetting();
});
