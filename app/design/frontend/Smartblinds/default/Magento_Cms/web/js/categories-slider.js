define([
    'jquery',
    'swiper'
], function ($, Swiper) {
    'use strict';

    return function (config, element) {
        const categoriesSlider = new Swiper(element, {
            navigation: {
                nextEl: '.category-slider-wrapper .swiper-button-next',
                prevEl: '.category-slider-wrapper .swiper-button-prev',
            },
            scrollbar: {
                el: ".category-slider-wrapper .swiper-scrollbar"
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
