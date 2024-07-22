define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        const video = $(element);

        video.find('source').each(function () {
            $(this).attr('src', $(this).attr('data-src'));
        });

        element.load();

        const $poster = $('#hero-poster');
        if ($poster.length) {
            element.addEventListener('loadeddata', (event) => {
                $poster.hide();
                element.style.display = 'block';
            });
        }
    }

});
