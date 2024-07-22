define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/mage',
    'domReady!'
], function (
    $,
    _,
    $t
) {
    'use strict';

    var $window = $(window);

    $('.product-options-wrapper .pdp-options-save-button .btn').on('click', function(event) {
        let widthInput = $(".width-height-option .control input[data-role=\"width\"]");
        let heightInput = $(".width-height-option .control input[data-role=\"height\"]");
        if (widthInput.length > 0 && heightInput.length > 0 && !widthInput.val() && !heightInput.val()) {
            widthInput.focus();
            widthInput.blur();
        }
    });

    $(function(){
        setTimeout(function () {
            $(window).trigger('swatches-click');
        },1500);
    });

    $.widget('mage.progressButton', {
        options: {
            progressButtonSelector: '[data-role=progress-button]',
            progressStepSelector: '[data-role=step]',
            boxToCartBottomSelector: '[data-role=box-tocart-bottom]',
            addToCartButtonSelector: '.action.tocart',
            productTopOptionSelector: '.product-option.top',
            productInfoDiscountMessageSelector: '[data-role=product-info-discount-message]',
            addToCartQtySelector: '[data-role=addtocart-qty]',
            textualOptionSelector: '.product-option.textual',
            configuratorDescriptionSelector: '[data-role=configurator-description]',
            priceLabelSelector: '.price-label',
            choicesSelector: '[data-role=choices]',
            choicesSwiper: '.choices-swiper.swiper-container',
            swatchOptionsSelector: 'div[data-role="swatch-options"]',
            addToCartTopContainer: '.product-add-form .product-addtocart-top-block',
            addToCartBottomContainer: '.product-add-form .product-options-bottom',
            widthFieldSelector: 'input[data-role=width]',
            heightFieldSelector: 'input[data-role=height]'
        },

        _create: function () {
            this.$progressButton = $(this.options.progressButtonSelector);
            this.$priceLabel = $(this.options.priceLabelSelector);
            this.$widthField = $(this.options.widthFieldSelector);
            this.$heightField = $(this.options.heightFieldSelector);
            this.$progressButton.click(this._startConfiguration.bind(this));
            this.isConfigurePage = $('body').hasClass('checkout-cart-configure');
            if (this.isConfigurePage) {
                this.$progressButton.click();
            }
        },

        _startConfiguration: function () {
            if (!this.isConfigurePage && (!this.$widthField.valid() || !this.$heightField.valid())) {
                return;
            }

            this.$progressButton.text($t('Update dimensions'));
            this.$progressButton.addClass('progress-btn');

            $(this.options.addToCartTopContainer).addClass('progress');
            $(this.options.addToCartBottomContainer).addClass('progress');
            $(this.options.boxToCartBottomSelector).addClass('completed');

            var $elementsToShow = [
                $(this.options.addToCartQtySelector),
                $(this.options.boxToCartBottomSelector),
                $(this.options.configuratorDescriptionSelector),
                $(this.options.productInfoDiscountMessageSelector),
                $(this.options.addToCartButtonSelector)
            ];
            _.each($elementsToShow, function ($element) {
                $element.show();
            });

            this.$priceLabel.text($t('Your price'));
            this.updateSteps();
            $window.on('update-steps', this.updateSteps.bind(this));
            $window.on('updatePrice', this.updateSteps.bind(this));
            this.$progressButton.unbind('click');
            this.$progressButton.click(this._updateConfiguration.bind(this));
        },

        _updateConfiguration: function () {
            let montages = $('.mageworx-swatch-option[data-option-code="montage"]');
            let countMontage = montages.length;
            if( countMontage > 0) {
                let i=0;
                let isSelected = false;
                for(i=0; i < countMontage; i++) {
                    let montage  = montages.eq(i);
                    if(montage.hasClass('selected')) {
                        isSelected = true;
                    }
                }
                let elOption = $('.product-option-header label[for="select_'+montages.eq(0).attr("data-option-id")+'"]').parent().parent();
                if(isSelected === false) {
                    if(elOption.find('.control .mage-error').length === 0){
                        elOption.find('.control').prepend('<div class="mage-error" generated="true"></div>');
                        elOption.find('.control .mage-error').text($t('Please pick a installation type to configure your product.'));
                    }
                } else if(elOption.find('.control .mage-error').length > 0) {
                    elOption.find('.control .mage-error').remove();
                } else {
                    let widthInput = $(".width-height-option .control input[data-role=\"width\"]");
                    let heightInput = $(".width-height-option .control input[data-role=\"height\"]");
                    if(widthInput.length > 0 && heightInput.length > 0 && !widthInput.val() && !heightInput.val()){
                        widthInput.focus();
                        widthInput.blur();
                    }
                }
            }
        },

        updateSteps: function () {
            if (this.stepsUpdated) {
                return;
            }

            var $progressSteps = $(this.options.progressStepSelector);
            _.each($progressSteps, function (stepElem) {
                var $stepElem = $(stepElem);
                $stepElem.show();
            }.bind(this));

            // fix for lazyload to show images after step show when user not scrolled
            window.dispatchEvent(new Event('scroll'));

            $window.trigger('load-choices');
            var $elementsToShow = [
                $(this.options.textualOptionSelector),
                $(this.options.choicesSelector),
                $(this.options.choicesSwiper)
            ];
            _.each($elementsToShow, function ($element) {
                $element.show();
            });

            this.$priceLabel.text($t('Your total price'));

            this.stepsUpdated = true;
        },

        isStepCompleted: function ($element) {
            return Boolean($element.find('.selected').length);
        }
    });

    return $.mage.progressButton;
});
