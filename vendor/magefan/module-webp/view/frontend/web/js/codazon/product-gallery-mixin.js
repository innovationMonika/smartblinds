/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define(['jquery'], function ($) {
    'use strict';

    var imggallery = {
      
         _create: function() {
            if (this.options.images.length && MagefanWebP.canUseWebP()) {
                for (let i in this.options.images) {
                    if (this.options.images[i].large) {
                        this.options.images[i].large = MagefanWebP.getWebUrl(this.options.images[i].large);
                    }
                    
                    if (this.options.images[i].small) {
                        this.options.images[i].small = MagefanWebP.getWebUrl(this.options.images[i].small);
                    }
                }
            }

            return this._super();
        }
    };

    return function (targetWidget) {

        $.widget('codazon.imggallery', targetWidget, imggallery);

        return $.codazon.imggallery;
    };

});