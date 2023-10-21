import DiscountComponent from './components/DiscountComponent'

if (typeof vueApp !== 'undefined') {
    vueApp.booting((vue) => {
        vue.component('discount-component', DiscountComponent)
    })
}
