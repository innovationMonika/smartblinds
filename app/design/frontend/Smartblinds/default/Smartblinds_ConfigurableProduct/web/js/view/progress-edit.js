define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/mage',
    'Smartblinds_ConfigurableProduct/js/view/progress',
    "Magento_Swatches/js/swatch-renderer",
    "Smartblinds_Options/js/price-option/width-height"
], function (
    $,
    _,
    $t
) {
    'use strict';

    var $window = $(window);

    $.widget('mage.progressEdit', $.mage.progressButton, {

        _create: function () {
            this.openSteps();
            this.$progressButton = $(this.options.progressButtonSelector);
            this.$progressButton.hide();
            this.$priceLabel = $(this.options.priceLabelSelector);
            $(this.options.smartblindsPriceSelector).show();
            $(this.options.addToCartTopContainer).addClass('progress');
            $(this.options.addToCartBottomContainer).addClass('progress');
            $(this.options.configuratorDescriptionSelector).show();
            $(this.options.productInfoDiscountMessageSelector).show();
            $(this.options.defaultSampleSelector).hide();
            this.$priceLabel.text($t('Your total price'));

            $window.trigger('priceOptionWidthHeightUpdate');
            $window.trigger('priceOptionWidthHeightValidate');
            $window.trigger('priceOptionWidthHeightSendUpdate');

            $window.on('update-steps', this.updateSteps.bind(this));

            $window.trigger('optionBaseProcessApplyChanges');
        },

        openSteps: function () {
            let $steps = [
                    this.options.progressStepSelector,
                    this.options.textualOptionSelector,
                    this.options.addToCartQtySelector,
                    this.options.addToCartButtonSelector,
                    this.options.choicesSelector,
                    this.options.choicesSwiper
                ];
            _.each($steps, function (el) {
                let $el = $(el);
                if(el === this.options.textualOptionSelector) {
                    let $input = $el.find('input[type="text"]');
                    if($input.length && $input.attr('data-value') !== '' && $input.val() === ''){
                        $input.val($input.attr('data-value'));
                    }
                }
                $el.show();
            }.bind(this));
            $(this.options.boxToCartBottomSelector).addClass('completed').show();
        }
    });

    return $.mage.progressEdit;
});
