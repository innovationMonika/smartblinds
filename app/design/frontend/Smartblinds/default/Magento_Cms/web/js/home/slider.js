define([
    'jquery',
    'swiper'
], function ($, Swiper) {
    'use strict';

    return function (config, element) {
        const homeCategories = new Swiper(element, {
            loop: true,
            navigation: {
                nextEl: '.home-slider .swiper-button-next',
                prevEl: '.home-slider .swiper-button-prev',
            }
        });
    }

});
