/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Customer/js/customer-data'
], function (_, resourceUrlManager, quote, storage, totalsService, errorProcessor, cartCache, customerData) {
    'use strict';

    /**
     * Load data from server.
     *
     * @param {Object} address
     */
    var loadFromServer = function (address) {
        var serviceUrl,
            payload;

        // Start loader for totals block
        totalsService.isLoading(true);
        serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote);
        payload = {
            addressInformation: {
                address: _.pick(address, cartCache.requiredFields)
            }
        };

        if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
            payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
            payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
        }

        return storage.post(
            serviceUrl, JSON.stringify(payload), false
        ).done(function (result) {
            var data = {
                totals: result,
                address: address,
                cartVersion: customerData.get('cart')()['data_id'],
                shippingMethodCode: null,
                shippingCarrierCode: null
            };

            if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                data.shippingMethodCode = quote.shippingMethod()['method_code'];
                data.shippingCarrierCode = quote.shippingMethod()['carrier_code'];
            }

            if(window.quoteItemCanOptionRemove && window.quoteItemCanOptionRemove.value && window.quoteItemCanOptionRemove.label) {
                let itemsCount = result.items.length;
                let i = 0;
                for (i = 0; i < itemsCount; i++) {
                    let itemsOptions = JSON.parse(result.items[i].options);
                    let itemsOptionCount = itemsOptions.length;
                    let iOption = 0;
                    for (iOption = 0; iOption < itemsOptionCount; iOption++) {
                        if (itemsOptions[iOption].value === window.quoteItemCanOptionRemove.value && itemsOptions[iOption].label === window.quoteItemCanOptionRemove.label) {
                            itemsOptions.splice(iOption, 1);
                            result.items[i].options = JSON.stringify(itemsOptions);
                            break;
                        }
                    }
                }
            }

            quote.setTotals(result);
            cartCache.set('cart-data', data);
        }).fail(function (response) {
            errorProcessor.process(response);
        }).always(function () {
            // Stop loader for totals block
            totalsService.isLoading(false);
        });
    };

    return {
        /**
         * Array of required address fields.
         * @property {Array.String} requiredFields
         * @deprecated Use cart cache.
         */
        requiredFields: cartCache.requiredFields,

        /**
         * Get shipping rates for specified address.
         * @param {Object} address
         */
        estimateTotals: function (address) {
            var data = {
                shippingMethodCode: null,
                shippingCarrierCode: null
            };

            if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                data.shippingMethodCode = quote.shippingMethod()['method_code'];
                data.shippingCarrierCode = quote.shippingMethod()['carrier_code'];
            }

            return loadFromServer(address);
        }
    };
});
