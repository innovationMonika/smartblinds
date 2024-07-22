/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/smart-keyboard-handler',
    'matchMedia',
    'mage/translate',
    'mage/mage',
    'mage/sticky',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler, mediaCheck, $t) {
    'use strict';

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');

    keyboardHandler.apply();

    /* Footer Accordion */

    if ($('.footer-links-right-title').length) {
        $('.footer-links-right-title').on('click', function () {
            if ($('body').width() < 1024) {
                $('.footer-links-right-title._opened').not($(this)).removeClass('_opened').parent().find('.footer-links-right-content').slideUp();
                $(this).toggleClass('_opened').parent().find('.footer-links-right-content').slideToggle();
            }
        });
    }

    /* Footer Move blocks */

    mediaCheck({
        media: '(min-width: 1024px)',
        // Switch to Desktop Version
        entry: function () {
            if ($('.footer-top .footer-social').length) {
                $('.footer-social').appendTo('.footer-bottom-right');
            }
        },
        // Switch to Mobile Version
        exit: function () {
            if ($('.footer-bottom .footer-social').length) {
                $('.footer-social').appendTo('.footer-top');
            }
        }
    });

    /* Fixed Header */
    if ($('.page-header').length) {

        var doc = document.documentElement,
            w = window,
            curScroll,
            prevScroll = w.scrollY || doc.scrollTop,
            curDirection = 0,
            prevDirection = 0,
            header = $('.page-header'),
            topBanner = $('#top-notification-bar'),
            toggled,
            threshold = 200,
            fixedHeaderTimer,
            padder = $('.header-padder'),
            $panelWrapper = $('.panel.wrapper');

        var checkScroll = function () {
            curScroll = w.scrollY || doc.scrollTop;

            if (curScroll > 10) {
                // if (!header.hasClass('_transition')) {
                //     header.addClass('_transition');
                // }
                if (!$panelWrapper.hasClass('_transition')) {
                    $panelWrapper.addClass('_transition');
                }
                if (!header.hasClass('_fixed')) {
                    header.addClass('_fixed');
                    $('html').addClass('_has-fixed-header');
                }
            } else {
                if (header.hasClass('_fixed')) {
                    header.removeClass('_fixed');
                    $('html').removeClass('_has-fixed-header');
                }
            }

            if (curScroll > prevScroll) {
                // scrolled down
                curDirection = 2;
            } else {
                //scrolled up
                curDirection = 1;
            }

            if (curDirection !== prevDirection && !$('._main-menu-opened').length && !$('._search-opened').length) {
                toggled = toggleHeader();
            }

            prevScroll = curScroll;
            if (toggled) {
                prevDirection = curDirection;
            }
        };

        var toggleHeader = function () {
            toggled = true;
            if (curDirection === 2 && curScroll > threshold) {
                header.addClass('_hide');
                topBanner.addClass('_header-hide');
            } else if (curDirection === 1) {
                header.removeClass('_hide');
                topBanner.removeClass('_header-hide');
            } else {
                toggled = false;
            }
            return toggled;
        };

        $(w).on('scroll', function () {
            checkScroll();
        }).trigger('scroll');

    }

    /*base suggestions desktop*/
    $('.block-search-wrap-desktop .pas-input-text').on('focus', function () {
        $(this).parents('.block-search-wrap-desktop').addClass('input-focus');
    }).on('blur', function () {

    });
    $(document).mouseup(function(e) {
        var container = $(".block-search-wrap-desktop");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.removeClass('input-focus');
        }
    });

    /* Category filter */
    $('.mobile-toggle-filter, .fly-filter-button').on('click', function (){
        $('.block.filter').show();
        $('body').addClass('mobile-filter-open');

        $('.amshopby-button').on('click',function (){
            $('body').removeClass('mobile-filter-open');
            $('.block.filter').hide();
        });
    });

    $('.close-mobile-filter-icon').on('click', function (){
        $('.block.filter').hide();
        $('body').removeClass('mobile-filter-open');
        $('.am_shopby_apply_filters').removeClass('visible');
    });

    $('.messages').click(function(){
        $('.message', this).hide();
    });
    $(window).on('scroll', function () {
       $('.message', this).hide();
    });


    $(document).ready(function() {
        let url = window.location.origin,
            domains = {
                'ned': 'smartblinds.nl',
                'bel': 'smartblinds.be',
                'com': 'smartblinds.com',
                'de': 'smartblinds.de',
                'at': 'smartblinds.at',
                'uk': 'smartblinds.co.uk'
            },
            domainId  = Object.keys(domains).find(key => url.includes(domains[key])) ?? null;
        if (domainId) {
            $.each(domains, function (id, value) {
                let newUrl = url.replace(domains[domainId], value);
                $('#lang-switcher .view-'+ id +' a').attr('href', newUrl);
            });
        }

    });
});
