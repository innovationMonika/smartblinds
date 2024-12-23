/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define(['jquery'], function ($) {
    'use strict';

    var notorama = {

        _initGalleryItems: function() {

            if (this.images && this.images.length) {
                if (!MagefanWebP.canUseWebP()) {
                    console.log("don't support webp");
                    for (var i = 0; i < this.images.length; i++) {
                        if (this.images[i].img && this.images[i].img.indexOf('/mf_webp/') != -1) {
                            this.images[i].img = MagefanWebP.getOriginWebPImage(this.images[i].img);
                            if (this.images[i].full) {
                                this.images[i].full = MagefanWebP.getOriginWebPImage(this.images[i].full);
                            }
                            if (this.images[i].thumb) {
                                this.images[i].thumb = MagefanWebP.getOriginWebPImage(this.images[i].thumb);
                            }
                        }
                    }
                }
            }

            return this._super();
        }
    };

    return function (targetWidget) {

        $.widget('aimes.notorama', targetWidget, notorama);

        return $.aimes.notorama;
    };
});
