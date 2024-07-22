define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils',
    'mage/template',
    'getSwatchSelectedProductId',
    'Smartblinds_Options/js/model/price-calculator',
], function (
    $,
    _,
    utils,
    mageTemplate,
    getSwatchSelectedProductId,
    PriceCalculator
) {
    'use strict';

    const $window = $(window);

    var mixin = {
        options: {
            initialPriceTemplate: '<span class="price"><%- data.formatted %></span><% if (data?.oldPrice) { %><br/><span class="old-price"><%- data.oldPrice.formatted %></span><% } %>',
        },

        _init: function initPriceBundle() {
            this._super();
            $window.on('optionBaseProcessApplyChanges', this.processApplyChanges.bind(this));
        },

        setProductFinalPrice: function (finalPrice) {
            var config = this.options,
                format = config.priceFormat,
                template = window?.curtainTracks ? config.priceTemplate : config.initialPriceTemplate,
                $pc = $(config.productPriceInfoSelector).find('[data-price-type="finalPrice"]'),
                templateData = {};

            if (_.isUndefined($pc)) {
                return;
            }

            if (finalPrice < 0) {
                finalPrice = 0;
            }

            if (!finalPrice) {
                return;
            }

            template = mageTemplate(template);
            templateData.data = {
                value: finalPrice,
                formatted: utils.formatPrice(finalPrice, format)
            };

            let regularPrice = getRegularPrice();
            if (!window?.curtainTracks) {
                let mageWorksOptions = $(".mageworx-swatch-container .mageworx-swatch-option");
                if (mageWorksOptions.length > 0) {
                    var self = this;
                    mageWorksOptions.each(function () {
                        let option = $(this);
                        if (!option.hasClass('selected')) {
                            return;
                        }
                        let optionPrice = option.attr('data-option-price');
                        if (!optionPrice) {
                            return;
                        }
                        optionPrice = parseFloat(optionPrice);
                        const isBedieningOption = option.data('option-code') === window.jsonConfig.bedieningOptionCode;
                        if (isBedieningOption) {
                            regularPrice += self.getBedieningOptionRegularPrice(option, optionPrice);
                        } else {
                            regularPrice += optionPrice;
                        }
                    });
                }
            }
            if (regularPrice && finalPrice < regularPrice) {
                templateData.data.oldPrice = {
                    value: regularPrice,
                    formatted: utils.formatPrice(regularPrice, format)
                };
            }

            $pc.html(template(templateData));
            $('[id=product-addtocart-button]').attr('data-price', finalPrice).data('price', finalPrice);

            $('[data-role=smartblinds-price]').show();
        },

        getBedieningOptionRegularPrice: function (option, optionPrice) {
            const systemTypeAttributeId = Object.values(window.jsonConfig.attributes).find(item => item.code === 'system_type').id;
            const isTdbuSelected = $('.swatch-attribute[data-attribute-id="' + systemTypeAttributeId + '"]').find('.swatch-option.selected[data-option-id="' + window.jsonConfig.systemTypeTdbuOptionId + '"]').length > 0;
            if (isTdbuSelected) {
                optionPrice *= 2;
            }
            return optionPrice;
        },

        setAdditionalProductFinalPrice: function (finalPrice) {},

        setProductPriceExclTax: function (priceExcludeTax) {},

        setAdditionalProductPriceExclTax: function (priceExcludeTax) {},

        setProductRegularPrice: function (regularPrice) {
            if (!window?.curtainTracks) {
                regularPrice = getRegularPrice();
            }

            var config = this.options,
                format = config.priceFormat,
                template = config.priceTemplate,
                $pc = $(config.productPriceInfoSelector).find('[data-price-type="oldPrice"]'),
                templateData = {};

            if (_.isUndefined($pc)) {
                return;
            }

            if (regularPrice < 0) {
                regularPrice = 0;
            }

            if (!regularPrice) {
                return;
            }

            template = mageTemplate(template);
            templateData.data = {
                value: regularPrice,
                formatted: utils.formatPrice(regularPrice, format)
            };

            $pc.html(template(templateData));
        },

        setAdditionalProductRegularPrice: function (regularPrice) {}
    };

    function getRegularPrice() {
        let price = null;
        if (window?.curtainTracks) {
            price = PriceCalculator.calculateCurtainTracksRegularPrice();
            return price ? price : window.curtainTracks.regularPrice;
        }

        price = PriceCalculator.calculateWidthHeightRegularPrice();

        if (price) {
            return price;
        }

        let productId = getSwatchSelectedProductId(),
            swatchConfig = window.jsonConfig,
            optionPrices = swatchConfig?.optionPrices ? swatchConfig.optionPrices : {},
            optionPricesRow = optionPrices.hasOwnProperty(productId) ?
                optionPrices[productId] : null;

        if (optionPricesRow) {
            return optionPricesRow.oldPrice.amount;
        }

        return null;
    }

    return function (targetWidget) {
        $.widget('mageworx.optionBase', targetWidget, mixin);
        return $.mageworx.optionBase;
    };
});
