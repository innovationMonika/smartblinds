/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SpecialPromotions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/recollect-shipping-rates'
], function ($, quote, urlManager, errorProcessor, messageContainer, storage, getPaymentInformationAction, totals, $t,
             fullScreenLoader, recollectShippingRates
) {
    'use strict';

    var successCallbacks = [],
        action,
        failCallbacks    = [],
        callSuccessCallbacks;

    /**
     * Execute callbacks when a coupon is successfully canceled.
     */
    callSuccessCallbacks = function () {
        successCallbacks.forEach(function (callback) {
            callback();
        });
    };

    /**
     * Cancel applied coupon.
     *
     * @param {Boolean} isApplied
     * @returns {Deferred}
     */
    action = function (isApplied) {
        var quoteId = quote.getQuoteId(),
            url     = urlManager.getCancelCouponUrl(quoteId),
            message = $t('Your coupon was successfully removed.'),
            specialDiscountInput = $('.mp-special-promotions-modal .coupon-box input');

        messageContainer.clear();
        fullScreenLoader.startLoader();

        return storage.delete(
            url,
            false
        ).done(function () {
            var deferred = $.Deferred();

            totals.isLoading(true);
            recollectShippingRates();
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                isApplied(false);
                totals.isLoading(false);
                fullScreenLoader.stopLoader();
                //Allowing to tap into coupon-cancel process.
                callSuccessCallbacks();
            });
            messageContainer.addSuccessMessage({
                'message': message
            });
        }).fail(function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();
            errorProcessor.process(response, messageContainer);
            failCallbacks.forEach(function (callback) {
                callback(response);
            });
            if(!isApplied() && quote.getTotals()()['coupon_code'] === undefined){
                specialDiscountInput.prop('disabled', false);
                specialDiscountInput.removeClass('_disabled');
            }
        });
    };

    /**
     * Callback for when the cancel-coupon process is finished.
     *
     * @param {Function} callback
     */
    action.registerSuccessCallback = function (callback) {
        successCallbacks.push(callback);
    };

    action.registerFailCallback = function (callback) {
        failCallbacks.push(callback);
    };

    action.resetCallBack = function () {
        successCallbacks = [];
        failCallbacks    = [];
    };

    return action;
});
