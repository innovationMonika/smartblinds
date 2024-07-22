define([
    'jquery',
    'underscore',
    'mage/mage',
    'Magento_Swatches/js/swatch-renderer'
], function ($, _) {
    'use strict';

    var $window = $(window);

    $.widget('mage.productTransparency', {
        options: {
            swatchOptionsSelector: 'div[data-role="swatch-options"]',
            transparencySelector: 'div[data-role="product-transparency"]',
            transparencyContainerSelector: 'div[data-role="product-transparency-container"]',
            swatchTransparencySelector: 'div[data-attribute-code="transparency"]'
        },

        _create: function () {
            this.$transparency = $(this.options.transparencySelector);
            this.$transparencyContainer = $(this.options.transparencyContainerSelector);
            this._updateTransparency();
            $(this.options.swatchOptionsSelector)
                .on('swatch.initialized', this._updateTransparency.bind(this));
            $window
                .on('swatches-click', this._updateTransparency.bind(this));
        },

        _updateTransparency: function () {
            var $selectedOption = $(this.options.swatchTransparencySelector)
                .find('.swatch-attribute-selected-option');
            var text = $selectedOption.text();
            if (text) {
                this.$transparency.text(text);
            }
            this.$transparencyContainer.css('visibility', 'visible');
        }
    });

    return $.mage.productTransparency;
});
