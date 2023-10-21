import DiscountVendor from './components/DiscountVendor';

if (typeof vueApp !== 'undefined') {
    vueApp.booting((vue) => {
        vue.component('discount-vendor-component', DiscountVendor);
    });
}
