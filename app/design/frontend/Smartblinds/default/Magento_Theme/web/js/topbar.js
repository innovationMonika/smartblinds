define([
    'jquery',
    'swiper'
], function ($, Swiper) {
    'use strict';

    return function (config, element) {
        const topbar = new Swiper(element, {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false
            }
        });
    }

});