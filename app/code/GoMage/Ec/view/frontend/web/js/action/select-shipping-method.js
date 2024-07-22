define(['jquery','mage/utils/wrapper', 'Magento_Checkout/js/model/quote'], function($, wrapper, quote)
{
    'use strict';

    return function(shippingMethod) {

        let methodCode = null;

        return wrapper.wrap(shippingMethod, function (originalAction, method) {
            if ( ('undefined' !== typeof dataLayer) && ('undefined' !== typeof data)
                && method && ('undefined' !== typeof method.method_code) && (methodCode !== method.method_code) ) {
                    (function(dataLayer, jQuery, shippingMethod) {
                        var method = '';
                        if (shippingMethod && shippingMethod.hasOwnProperty('method_title')) {
                            method = shippingMethod.method_title;
                        }
                        if ('undefined' !== typeof AEC && 'undefined' !== typeof AEC.Const) {
                            AEC.Checkout.stepOption(AEC.Const.CHECKOUT_STEP_SHIPPING, method);
                        }
                    })(dataLayer, jQuery, method);
                    methodCode = method.method_code;
            }

            return originalAction(method);
        });
    };
});
