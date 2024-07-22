define([
    'underscore',
    './option-params'
], function (
    _,
    OptionParams
) {
    'use strict'

    return {
        init: function ($widthField) {
            this.$widthField = $widthField;
        },

        calculateWidthHeightRegularPrice: function () {
            let price = calculateWidthHeightPrice();
            return price !== null ? configuratblePriceWithTax(price) : null;
        },

        calculateWidthHeightFinalPrice: function () {
            let price = calculateWidthHeightPrice();
            if (price !== null) {
                const
                    optionParams = OptionParams.get(),
                    basePrice = window.jsonConfig.optionPrices[optionParams.productId].basePrice.amount,
                    baseOldPrice = window.jsonConfig.optionPrices[optionParams.productId].baseOldPrice.amount,
                    discount = basePrice / baseOldPrice;
                price *= discount;
                return configuratblePriceWithTax(price);
            }
            return null;
        },

        calculateCurtainTracksRegularPrice: function () {
            let price = calculateCurtainTracksPrice(this.$widthField);
            return price !== null ? curtainTracksPriceWithTax(price) : null;
        },

        calculateCurtainTracksFinalPrice: function () {
            let price = calculateCurtainTracksPrice(this.$widthField);
            if (price !== null) {
                const discount = window.curtainTracks.finalPrice / window.curtainTracks.regularPrice;
                price *= discount;
                return curtainTracksPriceWithTax(price);
            }
            return null;
        }
    }

    function configuratblePriceWithTax(price) {
        return price + price * window.jsonConfig.taxRate / 100;
    }

    function curtainTracksPriceWithTax(price) {
        return price + price * window.curtainTracks.taxRate / 100
    }

    function calculateWidthHeightPrice() {
        const optionParams = OptionParams.get();

        if (!optionParams) {
            return null;
        }

        const jsonConfig = window.jsonConfig,
            width = optionParams.input.width,
            height = optionParams.input.height,
            fabricPrice = jsonConfig.optionPrices[optionParams.productId].smartblindsPrice.amount;

        const
            system = optionParams.system,
            price = (((width * height) / 1000000) * parseFloat(system.priceCoefficient) * fabricPrice)
                + ((width / 1000) * system.meterPrice) + system.basePrice;

        return price;
    }

    function calculateCurtainTracksPrice($widthField) {
        if (!$widthField) {
            return null;
        }
        const width = parseFloat($widthField.val());
        if (isNaN(width)) {
            return null;
        }

        let price = null;
        const prices = window.curtainTracks.prices;
        for (const [possibleWidth, possiblePrice] of Object.entries(prices)) {
            if (price === null && width <= possibleWidth) {
                price = possiblePrice;
            }
        }

        return price;
    }

});
