define([
    'jquery',
    'swiper'
], function ($, Swiper) {
    'use strict';

    return function (config, element) {
        const homeCategories = new Swiper(element, {
            navigation: {
                nextEl: '.home-categories .swiper-button-next',
                prevEl: '.home-categories .swiper-button-prev',
            },
            loop: false,
            slidesPerView: 'auto',
            spaceBetween: 20,
            breakpoints: {
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 29,
                    loop: false
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 29,
                    loop: false
                },
                578: {
                    slidesPerView: 2,
                    spaceBetween: 29,
                    loop: false
                }
            }
        });
    }

});
