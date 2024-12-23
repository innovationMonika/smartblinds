/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return function (targetModule) {

        return targetModule.extend({

            initialize: function (config, element) {
                var self = this;

                if (config.data && config.data.length) {
                
                    if (!MagefanWebP.canUseWebP()) {
                        console.log("don't support webp");
                        for (var i = 0; i < config.data.length; i++) {

                            if (config.data[i].img && config.data[i].img.indexOf('/mf_webp/') != -1) {
                                config.data[i].img = MagefanWebP.getOriginWebPImage(config.data[i].img);
                                if (config.data[i].full) {
                                    config.data[i].full = MagefanWebP.getOriginWebPImage(config.data[i].full);
                                }
                                if (config.data[i].thumb) {
                                    config.data[i].thumb = MagefanWebP.getOriginWebPImage(config.data[i].thumb);
                                }
                            }
                        }
                    }
                }

                return this._super(config, element);
            }
        });

    };
});
