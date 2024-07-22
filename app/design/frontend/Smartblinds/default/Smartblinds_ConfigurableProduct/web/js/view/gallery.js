define([
    'jquery',
    'underscore',
    'getSwatchSelectedProductId',
    'swiper',
    'mage/mage'
], function (
    $,
    _,
    getSwatchSelectedProductId,
    Swiper
) {
    'use strict';

    var $window = $(window);

    $.widget('mage.productChoicesGallery', {
        options: {
            selectors: {
                swatchOptions: 'div[data-role="swatch-options"]'
            }
        },

        _create: function () {
            this.$choicesGallery = $(this.options.selectors.choicesGallery);
            $window.on('swatches-click', this._rebuildGallery.bind(this));
            $('.choices-swiper').on('click','.swiper-slide', this._openModal.bind(this));
            $window.on('load-choices', () => {
                this.canLoadChoices = true;
                this._rebuildGallery();
            });
        },

        _openModal: function () {
            $('.fotorama__stage__frame').click();
        },

        _rebuildGallery: function () {
            if (this.options.isChoices && !this.canLoadChoices) {
                return;
            }
            const
                swatchConfig = window.jsonConfig,
                selectedProductId = getSwatchSelectedProductId(),
                images = swatchConfig.images?.[selectedProductId];
            if (images === undefined) {
                return;
            }
            let galleryHtml = '';
            images.forEach(image => {
                galleryHtml += this._getImageHtml(image);
            })
            this.$choicesGallery.html(galleryHtml);
            this.createGallery();
        },

        _getImageHtml: function (image) {
            return '<div class="swiper-slide"><img src="' + image.img + '" width="220" height="220" /></div>';
        },

        createGallery: function () {
            new Swiper(".choices-swiper", {
                slidesPerView: 3,
                observer: true,
                spaceBetween: 10
            });
        }
    });

    return $.mage.productChoicesGallery;
});
