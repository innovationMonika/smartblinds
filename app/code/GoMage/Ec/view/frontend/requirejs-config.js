var config = {
    map: {
        '*': {
            sampleBasketItemEvent: 'GoMage_Ec/js/event/sample/basket/item'
        }
    },
    deps: [
        'sampleBasketItemEvent'
    ],
    config: {
        mixins: {
            'Magento_Checkout/js/action/select-shipping-method': {
                'Anowave_Ec/js/action/select-shipping-method': false,
                'GoMage_Ec/js/action/select-shipping-method': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Anowave_Ec/js/swatch-renderer': false
            },
            'Smartblinds_ConfigurableProduct/js/view/progress': {
                'GoMage_Ec/js/view/progress-mixin': true
            },
            'Magento_Checkout/js/action/select-payment-method': {
                'Anowave_Ec/js/action/select-payment-method': false,
                'GoMage_Ec/js/action/select-payment-method': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Anowave_Ec/js/action/place-order': false,
                'GoMage_Ec/js/action/place-order': true
            }
        }
    }
};
