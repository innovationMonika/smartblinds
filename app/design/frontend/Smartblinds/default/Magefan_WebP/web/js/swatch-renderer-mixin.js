/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {

            /**
             * Update [gallery-placeholder] or [product-image-photo]
             * @param {Array} images
             * @param {jQuery} context
             * @param {Boolean} isInProductView
             */
            updateBaseImage: function (images, context, isInProductView, eventName) {
                var canUseWebP = MagefanWebP.canUseWebP();

                if (!canUseWebP) {
                    console.log("don't support webp (swatch)");
                    for (var i = 0; i < images.length; i++) {

                        if (images[i].img && images[i].img.indexOf('/mf_webp/') != -1) {
                            images[i].img = MagefanWebP.getOriginWebPImage(images[i].img);
                            if (images[i].full) {
                                images[i].full = MagefanWebP.getOriginWebPImage(images[i].full);
                            }
                            if (images[i].thumb) {
                                images[i].thumb = MagefanWebP.getOriginWebPImage(images[i].thumb);
                            }
                        }
                    }
                }

                this._super(images, context, isInProductView, eventName);

                if (canUseWebP) {
                    if (!isInProductView) {
                        var justAnImage = images[0];
                        if (justAnImage && justAnImage.img) {
                            // here we changing picture src
                            context.find('.product-image-photo').parent().find('source').attr('srcset', justAnImage.img);
                        }
                    }
                }
            }

        });

        return $.mage.SwatchRenderer;
    }
});
