define([
    'jquery',
    'tabs'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).tabs({
            'openedState': '_active'
        })
        $(element).find('.home-tabs-arrow').on('click', function () {
            let tabs = $('.home-tab'),
                activeTab = tabs.filter('._active');
            if ($(this).hasClass('home-tabs-arrow-prev')) {
                if (activeTab.prev().length) {
                    activeTab.prev().find('.home-tab-link').trigger('click');
                } else {
                    tabs.filter(':last-child').find('.home-tab-link').trigger('click');
                }
            } else {
                if (activeTab.next().length) {
                    activeTab.next().find('.home-tab-link').trigger('click');
                } else {
                    tabs.filter(':first-child').find('.home-tab-link').trigger('click');
                }
            }
        });
    }

});
