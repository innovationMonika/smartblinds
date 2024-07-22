define([
    'jquery',
    'underscore',
    'mage/mage',
    'Magento_Swatches/js/swatch-renderer'
], function ($, _) {
    'use strict';

    var $window = $(window);

    $.widget('mage.productColor', {
        options: {
            swatchOptionsSelector: 'div[data-role="swatch-options"]',
            productColorSelector: 'div[data-role="product-color"]',
            transparencyContainerSelector: 'div[data-role="product-transparency-container"]',
            swatchColorSelector: 'div[data-attribute-code="color"]'
        },

        _create: function () {
            this.$productColor = $(this.options.productColorSelector);
            this.$transparencyContainer = $(this.options.transparencyContainerSelector);
            this._updateProductColor();
            $(this.options.swatchOptionsSelector)
                .on('swatch.initialized', this._updateProductColor.bind(this));
            $window
                .on('swatches-click', this._updateProductColor.bind(this));
        },

        _updateProductColor: function () {
            var $selectedOption = $(this.options.swatchColorSelector)
                .find('.swatch-attribute-selected-option');
            var text = $selectedOption.text();
            if (text) {
                this.$productColor.text(text);
            }
        }
    });

    return $.mage.productColor;
});
