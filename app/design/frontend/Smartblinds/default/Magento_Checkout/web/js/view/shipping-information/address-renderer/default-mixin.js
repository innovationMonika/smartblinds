define([
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data'
], function (Component, _, customerData) {
    'use strict';

    var customer = customerData.get('customer')(),
        customerr = customerData.get('checkout-data')(),
        customerEmail = null;

    var mixin = {

        getCustomerEmail: function () {
        
            if (customer.email) {
                customerEmail = customer.email;
            } else {
                customerEmail = customerr.inputFieldEmailValue;
            }

            return customerEmail;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
