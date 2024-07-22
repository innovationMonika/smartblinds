define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {

        let openSearch = $('[data-action="open-search"]'),
            closeSearch = $('[data-action="close-search"]'),
            backToMenu = $('[data-action="back-to-menu"]'),
            containers = $('.page-header, .block-search-wrap');

        function checkScroll() {
            let body = $('body'),
                normalWidth = window.innerWidth,
                scrollWidth = normalWidth - body.width();

            if (normalWidth > body.width()) {
                if (body.width() > 1663) {
                    containers.css({
                        'left': '-' + scrollWidth + 'px'
                    });
                } else {
                    $('.header.content').css({
                        'margin-right': scrollWidth
                    });
                    $('.panel.header').css({
                        'padding-right': scrollWidth + 20
                    });
                }
            } else {
                containers.add('.header.content, .panel.header').removeAttr('style');
            }
        }

        if (openSearch.length) {
            openSearch.on('click', function (event) {
                if (!$('html').hasClass('_main-menu-opened')) {
                    checkScroll();
                    $('html').addClass('_search-opened');
                } else {
                    if ($('.page-header').css('left') != 0) {
                        $('.block-search-wrap').css({
                            'left': $('.page-header').css('left')
                        });
                    }
                    $('html').addClass('_search-opened');
                }
            });
        }

        if (closeSearch.length) {
            closeSearch.on('click', function (event) {
                if ($('.block-search-clear:visible').length) {
                    $('.block-search-clear').trigger('click');
                }
                containers.add('.main-menu-wrap, .header.content, .panel.header').removeAttr('style');
                $('html').removeClass('_search-opened _main-menu-opened');
            });
        }

        if (backToMenu.length) {
            backToMenu.on('click', function (event) {
                if ($('.block-search-clear:visible').length) {
                    $('.block-search-clear').trigger('click');
                }
                $('html').removeClass('_search-opened');
            });
        }

    }

});
