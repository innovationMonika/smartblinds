define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/view/shipping-information'
], function ($, Component, quote, customerData, stepNavigator) {
    'use strict';

    var countryData = customerData.get('directory-data'),
        customer = customerData.get('customer'),
        checkoutData = customerData.get('checkout-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/billing-address/billing-information'
        },
        currentBillingAddress: quote.billingAddress,

        /** @inheritdoc */
        initialize: function () {
            this._super();

            return this;
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return stepNavigator.isProcessed('shipping');
        },

        /**
         * @return {Boolean}
         */
        isBillingNotSameAsShipping: function () {
            if (quote.billingAddress() != null) {
                if (quote.billingAddress().getCacheKey() != quote.shippingAddress().getCacheKey()) {
                    return true;
                }
            }

            return false;
        },

        /**
         * @param {Number} countryId
         * @return {*}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        getCustomerEmail: function () {
            let customerEmail = '';

            if (customer().email) {
                customerEmail = customer().email;
            } else {
                customerEmail = checkoutData().inputFieldEmailValue;
            }

            return customerEmail;
        }
    });
});
