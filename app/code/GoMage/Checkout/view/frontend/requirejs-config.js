/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            'Magento_Checkout/js/model/cart/estimate-service':'GoMage_Checkout/js/model/cart/estimate-service',
            'Magento_Tax/js/view/checkout/summary/tax':'GoMage_Checkout/js/view/checkout/summary/tax'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'GoMage_Checkout/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                "GoMage_Checkout/js/view/inline-validation": true,
            },
        }
    }
};
