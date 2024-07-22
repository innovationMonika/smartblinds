define([
    'jquery',
    'underscore',
    'priceUtils',
    'mage/translate',
    'Smartblinds_Options/js/model/price-calculator',
    'mage/validation',
    'priceOptions',
    'jquery-ui-modules/widget'
], function (
    $,
    _,
    utils,
    $t,
    PriceCalculator
) {
    'use strict';

    var $window = $(window);

    $.widget('mage.priceOptionCurtainTracksWidth', {
        options: {
            formSelector: '#product_addtocart_form',
            widthFieldSelector: 'input[data-role=curtain_tracks_width]'
        },

        _create: function () {
            this
                ._initFields()
                ._initEvents()
                ._addValidatorMethod()
                ._initOptionHandler();
        },

        _initFields: function () {
            this.form = this.element.closest(this.options.formSelector);
            this.$form = $(this.form);
            this.$widthField = $(this.element.find(this.options.widthFieldSelector));
            PriceCalculator.init(this.$widthField);
            return this;
        },

        _initEvents: function () {
            this.$widthField.on('focusout', this._validateField.bind(this));
            return this;
        },

        _validateField: function (event) {
            $.validator.validateSingleElement($(this.$widthField), {});
        },

        _initOptionHandler: function () {
            var handlerId = 'curtain_tracks_width',
                extendData = {
                    optionHandlers: {}
                };
            extendData.optionHandlers[handlerId] = this._optionHandler.bind(this);
            this.form.priceOptions(extendData);
            return this;
        },

        _optionHandler: function (element, optionConfig) {
            var optionId = utils.findOptionId(element),
                overhead = optionConfig[optionId].prices,
                changesOptionId = 'options[' + optionId + ']',
                changes = {};

            const price = PriceCalculator.calculateCurtainTracksFinalPrice();
            if (price === null) {
                changes[changesOptionId] = {};
                return changes;
            }
            const oldPrice = PriceCalculator.calculateCurtainTracksRegularPrice();

            overhead.basePrice.amount = price;
            overhead.finalPrice.amount = price;
            overhead.oldPrice.amount = oldPrice;
            overhead.oldPrice.amount_excl_tax = oldPrice;
            overhead.oldPrice.amount_incl_tax = oldPrice;
            changes[changesOptionId] = overhead;
            return changes;
        },

        _addValidatorMethod: function () {
            var self = this;
            $.validator.addMethod('curtain-tracks', function (value, element) {
                const $element = $(element);
                value = value.replace(',', '.');
                if (isNaN(value)) {
                    $element.data('error-message', $t('Wrong width provided'));
                    return false;
                }
                const width = parseInt(value);
                if (width < 50) {
                    $element.data('error-message', $t('Your product is too small. The minimum width is 50 cm.'));
                    return false;
                }
                if (width > 580) {
                    $element.data('error-message', $t('Your product is too broad and therefore cannot be delivered. The maximum width is 580 cm.'));
                    return false;
                }
                return true;
            }, function (params, element) {
                return $(element).data('error-message');
            });

            return this;
        }

    });

    return $.mage.priceOptionCurtainTracksWidth;
});
