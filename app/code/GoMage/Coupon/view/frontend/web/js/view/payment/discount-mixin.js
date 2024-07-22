define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/model/customer',
], function ($, Component, ko, customer) {
    'use strict';

    var mixin = {
        defaults: {
            template: 'GoMage_Coupon/payment/discount'
        },

        isDisplayed: function () {
            return customer.isLoggedIn();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
