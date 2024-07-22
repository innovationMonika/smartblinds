var config = {

    map: {
        '*': {
            'Magento_Checkout/js/view/summary/cart-items':
                'Magento_Checkout/js/view/summary/cart-items'
        }
    },

    config: {
        mixins: {
            'Ananta_ImprovedCheckout/js/view/shipping': {
                'Ananta_ImprovedCheckout/js/view/shipping-mixin': true
            }
        }
    }
};
