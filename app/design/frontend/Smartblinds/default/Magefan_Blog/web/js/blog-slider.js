define([
    'jquery',
    'swiper'
], function ($, Swiper) {
    'use strict';

    return function (config, element) {
        const blogSlider = new Swiper(element, {
            navigation: {
                nextEl: '.blog-slider .swiper-button-next',
                prevEl: '.blog-slider .swiper-button-prev',
            },
            loop: false,
            slidesPerView: 'auto',
            spaceBetween: 20,
            breakpoints: {
                768: {
                    slidesPerView: 3,
                    spaceBetween: 29,
                    loop: false
                }
            }
        });
    }

});
