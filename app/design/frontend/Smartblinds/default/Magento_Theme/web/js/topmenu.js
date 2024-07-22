define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {

        let navToggle = $('[data-action="toggle-nav"]'),
            navContainer = $(element),
            navParentLinks = navContainer.find('.parent'),
            navImages = $('.navigation-image'),
            navBackground = $('.navigation-background'),
            containers = $('.page-header, .main-menu-wrap');

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

        /* Nav toggle */
        if (navToggle.length) {
            navToggle.on('click', function () {
                if (!$('html').hasClass('_main-menu-opened')) {
                    checkScroll();
                } else {
                    containers.add('.block-search-wrap, .header.content, .panel.header').removeAttr('style');
                }
                $('html').toggleClass('_main-menu-opened');
            });
        }

        /* Create back links */
        navParentLinks.each(function () {
            let submenu = $(this).children('.submenu'),
                linkText = $(this).children('a').find('span').text();

            submenu.prepend('<li class="nav-back"><a href="#"><i class="far fa-long-arrow-left"></i><span class="text">' + linkText + '</span></a></li>');
        });

        /* Open submenu */
        navParentLinks.children('a').on('click', function (event) {
            event.preventDefault();

            let parent = $(this).parent();

            $(this).addClass('_hide');
            parent.siblings().addClass('_hide');
            parent.children('.submenu').addClass('_show');
        });

        /* Bind back links */
        navContainer.find('.nav-back a').on('click', function (event) {
            event.preventDefault();

            let parent = $(this).closest('.parent');

            parent.children('a').removeClass('_hide');
            parent.siblings().removeClass('_hide');
            parent.children('.submenu').removeClass('_show');
        });

        /* Change image and bg on hover */
        navContainer.find('a').on('mouseenter', function () {
            let parentClass = $(this).closest('li')[0].classList[1],
                navigationImage = $('[data-nav-image="' + parentClass + '"]');

            if (navigationImage.length) {
                navImages.removeClass('_show');
                navigationImage.addClass('_show');
                navBackground.css({
                    'background-image': 'url("' + navigationImage.find('img').attr('src') + '")'
                });
            }

        });

    }

});
