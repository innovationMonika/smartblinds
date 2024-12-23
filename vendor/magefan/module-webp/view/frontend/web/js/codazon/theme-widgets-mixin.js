/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define(['jquery'], function ($) {
    'use strict';

    var themewidgets = {
      
         _create: function() {
            let a = this.options;
            
            if (MagefanWebP.canUseWebP() && this.options.hasOwnProperty('codazon.slideshow') && this.options['codazon.slideshow'].items.length) {

                let codazonSlideshowItems = this.options['codazon.slideshow'].items;

                for (let i in codazonSlideshowItems) {
                    ['img', 'smallImg'].forEach((imageSize) => {
                        if (codazonSlideshowItems[i][imageSize]) {
                            let webpUrl = MagefanWebP.getWebUrl(codazonSlideshowItems[i][imageSize]);

                            if (webpUrl) {
                                codazonSlideshowItems[i][imageSize] = webpUrl;
                            }   
                        }
                    });
                }

                this.options['codazon.slideshow'].items = codazonSlideshowItems;
            }

            return this._super();
        }
    };

    return function (targetWidget) {
        $.widget('codazon.themewidgets', targetWidget, themewidgets)

        return $.codazon.themewidgets;
    };
});
