define([
    'jquery',
    'swiper'
], function ($, Swiper) {
    'use strict';

    return function (config, element) {
        const pdpSlider = new Swiper(element, {
            slidesPerView: 'auto',
            loop: false,
            freeMode: true,
            spaceBetween: 20,
            pagination: {
                el: ".pdp-grid-slider .swiper-pagination",
            },
            breakpoints: {
                769: {
                    slidesPerView: 2,
                    freeMode: false
                },
                1025: {
                    slidesPerView: 3,
                    freeMode: false,
                    spaceBetween: 30
                }
            }
        });
    }

});
