define([
    'jquery',
    'swiper'
], function($, Swiper) {
    'use strict';
    return function(config, element) {
        const instagramSlider = new Swiper(config.container + ' .swiper-container-instagram',{
            slidesPerView: 1,
            loop: true,
            freeMode: true,
            spaceBetween: 20,
            preloadImages: false,
            lazy: {
                checkInView: true
            },
            navigation: {
                nextEl: config.container + ' .swiper-button-next',
                prevEl: config.container + ' .swiper-button-prev'
            },
            breakpoints: {
                400: {
                    slidesPerView: 2
                },
                750: {
                    slidesPerView: 3
                },
                992: {
                    slidesPerView: 4
                }
            }
        });
    }
});
