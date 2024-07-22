define([
    'underscore',
    'getSwatchSelectedProductId',
    './system'
], function (
    _,
    getSwatchSelectedProductId,
    System
) {
    'use strict'

    return {
        init: function ($valueField) {
            this.$valueField = $valueField;
        },

        get: function (forProductId = null, overrideSystem = null) {
            if (!this.$valueField) {
                return null;
            }

            var jsonConfig = window.jsonConfig,
                productId = forProductId ? forProductId : parseInt(getSwatchSelectedProductId()),
                system = overrideSystem ? overrideSystem : System.get(),
                weight = productId ? jsonConfig.additionalAttributes[productId]['weight'] : null,
                thickness = productId ? jsonConfig.additionalAttributes[productId]['thickness'] : null,
                width = productId ? jsonConfig.additionalAttributes[productId]['width'] : null,
                railroad = productId ? jsonConfig.additionalAttributes[productId]['railroad'] : false,
                maxWidth = productId ? jsonConfig.additionalAttributes[productId]['max_width'] : null,
                maxHeight = productId ? jsonConfig.additionalAttributes[productId]['max_height'] : null,
                inputWidth = this.$valueField.data('width'),
                inputHeight = this.$valueField.data('height');

            if (!inputWidth && !inputHeight) {
                return null;
            }

            var requiredFields = [productId, system, weight, thickness, width],
                allFieldsFilled = true;

            if (system && (system.systemCategory === 'venetian_blinds' || system.systemCategory === 'honeycomb_blinds')) {
                maxWidth = system.maxWidth;
                maxHeight = system.maxHeight;
                requiredFields = [productId, system, weight];
            }


            requiredFields.forEach(function (item) {
                if (!item) {
                    allFieldsFilled = false;
                }
            });

            if (!allFieldsFilled || !this.$valueField) {
                return null;
            }

            return {
                productId: productId,
                system: system,
                product: {
                    weight: weight,
                    thickness: thickness,
                    width: width,
                    railroad: Boolean(railroad),
                    maxWidth: maxWidth,
                    maxHeight: maxHeight
                },
                input: {
                    width: parseInt(inputWidth),
                    height: parseInt(inputHeight)
                }
            }
        }
    }
});
