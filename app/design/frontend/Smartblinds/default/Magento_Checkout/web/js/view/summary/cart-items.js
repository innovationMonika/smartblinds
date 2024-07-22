define([
    'ko',
    'Magento_Checkout/js/model/totals',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote',
    'underscore',
    'mage/translate',
], function (ko, totals, Component, stepNavigator, quote, _, $t) {
    'use strict';

    var useQty = window.checkoutConfig.useQty;

    function IsJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/cart-items'
        },
        totals: totals.totals(),
        items: ko.observable([]),
        maxCartItemsToDisplay: window.checkoutConfig.maxCartItemsToDisplay,
        cartUrl: window.checkoutConfig.cartUrl,

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
        },

        /**
         * @deprecated Please use observable property (this.items())
         */
        getItems: totals.getItems(),

        /**
         * Returns cart items qty
         *
         * @returns {Number}
         */
        getItemsQty: function () {
            return parseFloat(this.totals['items_qty']);
        },

        /**
         * Returns count of cart line items
         *
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            return parseInt(totals.getItems()().length, 10);
        },

        /**
         * Returns shopping cart items summary (includes config settings)
         *
         * @returns {Number}
         */
        getCartSummaryItemsCount: function () {
            return useQty ? this.getItemsQty() : this.getCartLineItemsCount();
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            // Set initial items to observable field
            this.setItems(totals.getItems()());

            // Subscribe for items data changes and refresh items in view
            totals.getItems().subscribe(function (items) {
                this.setItems(items);
            }.bind(this));
        },

        /**
         * Set items to observable field
         *
         * @param {Object} items
         */
        setItems: function (items) {
            if (items && items.length > 0) {
                items = items.slice(parseInt(-this.maxCartItemsToDisplay, 10));
            }

            this.items(this.prepareProductOptions(items));
        },

        /**
         * prepare options
         *
         * @param {Object} items
         *  @returns {Object} items
         */
        prepareProductOptions: function (items) {
            _.each(items, function (elem) {
                var options = JSON.parse(elem.options);
                _.each(options, function (option, index) {
                    if(option) {
                        if (window.quoteItemCanOptionRemove
                            && window.quoteItemCanOptionRemove.label == option.label
                            && window.quoteItemCanOptionRemove.value == option.value
                        ) {
                            options.splice(index, 1);
                        } else {
                            if (IsJsonString(option.value.replace(/(&quot;)/gm, "\""))) {
                                var optionValue = JSON.parse(option.value.replace(/(&quot;)/gm, "\""));
                                var optText = [];
                                if (_.isObject(optionValue)) {
                                    _.each(optionValue, function (value, key) {
                                        optText.push($t(key) + ': ' + parseFloat(value / 10));
                                    });
                                    options[index].value = optText.join(', ');
                                }
                            }
                        }
                    }
                });
                elem.options = JSON.stringify(options);
            }, this);

            return items;
        },

        /**
         * Returns bool value for items block state (expanded or not)
         *
         * @returns {*|Boolean}
         */
        isItemsBlockExpanded: function () {
            return quote.isVirtual() || stepNavigator.isProcessed('shipping');
        }
    });
});
