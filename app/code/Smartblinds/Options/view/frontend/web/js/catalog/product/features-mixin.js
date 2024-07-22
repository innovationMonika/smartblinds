define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils',
    'getSwatchSelectedProductId',
    'priceUtils'
], function (
    $,
    _,
    utils,
    getSwatchSelectedProductId,
    priceUtils
) {
    'use strict';

    var mixin = {
        calculateSelectedOptionsPrice: function () {
            this._super();

            var self = this,
                form = this.base.getFormElement(),
                config = this.base.options,
                options = $(config.optionsSelector, form);

            options.filter('input[type="hidden"]').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    optionConfig = config.optionConfig && config.optionConfig[optionId],
                    value = $element.val();

                if ($element.closest('.field').css('display') === 'none') {
                    $element.val('');
                    return;
                }

                var productQty = $(config.productQtySelector).val();

                var form = $element.closest('#product_addtocart_form'),
                    handlerId = $element.data('role'),
                    handler = form.data('mage-priceOptions').options.optionHandlers[handlerId],
                    handlerOptionPrices = {}

                if (handler) {
                    var handlerOptionConfig = {};
                    handlerOptionConfig[optionId] = optionConfig;
                    handlerOptionPrices = handler($element, handlerOptionConfig);
                }

                var handlerPrices = handlerOptionPrices.hasOwnProperty($element.data('role')) ?
                    handlerOptionPrices[$element.data('role')] : null;
                var prices = handlerPrices ? handlerPrices : optionConfig.prices;

                var basePrice = prices.basePrice.amount;
                var finalPrice = prices.finalPrice.amount;

                self.optionFinalPrice += parseFloat(finalPrice) * productQty;
                self.optionOldPriceInclTax += parseFloat(prices.oldPrice.amount_incl_tax) * productQty;
                self.optionBasePrice += parseFloat(basePrice) * productQty;
                self.optionOldPriceExclTax += parseFloat(prices.oldPrice.amount_excl_tax) * productQty;

                self.optionFinalPricePerItem += parseFloat(finalPrice);
                self.optionOldPricePerItemInclTax += parseFloat(prices.oldPrice.amount_incl_tax);
                self.optionBasePricePerItem += parseFloat(basePrice);
                self.optionOldPricePerItemExclTax += parseFloat(prices.oldPrice.amount_excl_tax);
            });
        },

        collectOptionPriceAndQty: function calculateOptionsPrice(optionConfigCurrent, optionId, valueId)
        {
            this.actualPriceInclTax = 0;
            this.actualPriceExclTax = 0;

            var config = this.base.options,
                isOneTime = this.base.isOneTimeOption(optionId),
                productQty = $(config.productQtySelector).val(),
                qty = !_.isUndefined(optionConfigCurrent['qty']) ? optionConfigCurrent['qty'] : 1;
            this.getActualPrice(optionId, valueId, qty);
            if (productQty == 0) {
                productQty = 1;
            }

            var actualFinalPrice = this.actualPriceInclTax
                ? this.actualPriceInclTax
                : parseFloat(optionConfigCurrent.prices.finalPrice.amount),
                actualBasePrice = this.actualPriceExclTax
                    ? this.actualPriceExclTax
                    : parseFloat(optionConfigCurrent.prices.basePrice.amount),
                oldPriceInclTax = parseFloat(optionConfigCurrent.prices.oldPrice.amount_incl_tax),
                oldPriceExclTax = parseFloat(optionConfigCurrent.prices.oldPrice.amount_excl_tax),
                actualFinalPricePerItem = this.actualPriceInclTax
                    ? this.actualPriceInclTax
                    : parseFloat(optionConfigCurrent.prices.finalPrice.amount),
                actualBasePricePerItem = this.actualPriceExclTax
                    ? this.actualPriceExclTax
                    : parseFloat(optionConfigCurrent.prices.basePrice.amount),
                oldPricePerItemInclTax = parseFloat(optionConfigCurrent.prices.oldPrice.amount_incl_tax),
                oldPricePerItemExclTax = parseFloat(optionConfigCurrent.prices.oldPrice.amount_excl_tax);

            const optionPrices = jsonConfig.optionPrices[getSwatchSelectedProductId()];
            if (optionPrices && optionPrices.oldPrice.amount > 0) {
                const discount = optionPrices.finalPrice.amount / optionPrices.oldPrice.amount;
                actualFinalPricePerItem *= discount;
                actualBasePricePerItem *= discount;
                actualFinalPrice *= discount;
                actualBasePrice *= discount;
            }

            if (!isOneTime
                && (this.options.product_price_display_mode === 'final_price'
                    || this.options.additional_product_price_display_mode === 'final_price'
                )
            ) {
                actualFinalPrice *= productQty;
                actualBasePrice *= productQty;
                oldPriceInclTax *= productQty;
                oldPriceExclTax *= productQty;
            }

            const $option = $('.mageworx-swatch-option.selected[data-option-id="' + optionId + '"][data-option-type-id="' + valueId + '"]').first();
            const $doubleText = $option.closest('.product-option').find('span#doubletext');
            $option.closest('.product-option').find('span#value').removeClass('with-doubletext');
            if ($doubleText.length) {
                $doubleText.remove();
            }
            const isBedieningOption = $option.data('option-code') === window.jsonConfig.bedieningOptionCode;
            const systemTypeAttributeId = Object.values(window.jsonConfig.attributes).find(item => item.code === 'system_type')?.id;
            const isTdbuSelected = $('.swatch-attribute[data-attribute-id="' + systemTypeAttributeId + '"]').find('.swatch-option.selected[data-option-id="' + window.jsonConfig.systemTypeTdbuOptionId + '"]').length > 0;
            if (isBedieningOption && isTdbuSelected) {
                actualFinalPricePerItem *= 2;
                actualBasePricePerItem *= 2;
                oldPricePerItemInclTax *= 2;
                oldPricePerItemExclTax *= 2;
                actualFinalPrice *= 2;
                actualBasePrice *= 2;
                oldPriceInclTax *= 2;
                oldPriceExclTax *= 2;
                const valueSpanElement = $option.closest('.product-option').find('span#value');
                if (valueSpanElement.length && actualFinalPricePerItem > 0) {
                    const actualFinalPricePerItemFormatted = priceUtils.formatPrice(actualFinalPricePerItem, window.jsonConfig.currencyFormat);
                    let optionLabel = $option.data('option-label');
                    valueSpanElement.addClass('with-doubletext').append('<span id="doubletext">' + optionLabel + ' +' + actualFinalPricePerItemFormatted + '</span>');
                }
            }

            this.optionFinalPricePerItem += actualFinalPricePerItem * qty;
            this.optionBasePricePerItem += actualBasePricePerItem * qty;
            this.optionOldPricePerItemInclTax += oldPricePerItemInclTax * qty;
            this.optionOldPricePerItemExclTax += oldPricePerItemExclTax * qty;

            this.optionFinalPrice += actualFinalPrice * qty;
            this.optionBasePrice += actualBasePrice * qty;
            this.optionOldPriceInclTax += oldPriceInclTax * qty;
            this.optionOldPriceExclTax += oldPriceExclTax * qty;
        },

        initProductPrice: function (productConfig)
        {
            if (!this.swatchesNotSubscribed) {
                var $swatchOptions = $('div[data-role="swatch-options"]');
                $swatchOptions.on('swatch.initialized', () => {
                    $(window).trigger('swatches-click');
                });
                this.swatchesNotSubscribed = true;
            }

            let productId = getSwatchSelectedProductId(),
                swatchConfig = window.jsonConfig,
                optionPrices = swatchConfig?.optionPrices ? swatchConfig.optionPrices : {},
                optionPricesRow = optionPrices.hasOwnProperty(productId) ?
                    optionPrices[productId] : null;

            if (optionPricesRow) {
                productConfig.regular_price_excl_tax = optionPricesRow.baseOldPrice.amount;
                productConfig.regular_price_incl_tax = optionPricesRow.oldPrice.amount;
                productConfig.final_price_excl_tax = optionPricesRow.basePrice.amount;
                productConfig.final_price_incl_tax = optionPricesRow.finalPrice.amount;
            }

            this.productDefaultRegularPriceExclTax = productConfig.regular_price_excl_tax;
            this.productDefaultRegularPriceInclTax = productConfig.regular_price_incl_tax;
            this.productDefaultFinalPriceExclTax = productConfig.final_price_excl_tax;
            this.productDefaultFinalPriceInclTax = productConfig.final_price_incl_tax;

            this.productPerItemRegularPriceExclTax = productConfig.regular_price_excl_tax;
            this.productPerItemRegularPriceInclTax = productConfig.regular_price_incl_tax;
            this.productPerItemFinalPriceExclTax = productConfig.final_price_excl_tax;
            this.productPerItemFinalPriceInclTax = productConfig.final_price_incl_tax;

            this.productTotalRegularPriceExclTax = productConfig.regular_price_excl_tax;
            this.productTotalRegularPriceInclTax = productConfig.regular_price_incl_tax;
            this.productTotalFinalPriceExclTax = productConfig.final_price_excl_tax;
            this.productTotalFinalPriceInclTax = productConfig.final_price_incl_tax;
        }
    };

    return function (targetWidget) {
        $.widget('mageworx.optionFeatures', targetWidget, mixin);
        return $.mageworx.optionFeatures;
    };
});
