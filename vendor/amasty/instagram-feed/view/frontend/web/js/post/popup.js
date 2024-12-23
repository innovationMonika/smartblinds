define([
    'jquery',
    'Amasty_InstagramFeed/vendor/fancybox/jquery.fancyambox.min'
], function ($) {
    'use strict';

    $.widget('mage.amInstPostPopup', {
        itemSelector: "[data-aminst-js='post-item']",
        feedLinkSelector: '[data-aminst-js="feed-link"]',

        _create: function () {
            if (this.options.element) {
                this.element = this.options.element;
            }

            $(this.element).find(this.feedLinkSelector).on('click', this._showPopup.bind(this));
        },

        _showPopup: function (event) {
            var postUrl = $(this.element).find(this.feedLinkSelector).attr('href'),
                itemsCont = $(this.element.closest("[data-aminst-js='post-items']"));

            if (postUrl) {
                event.stopPropagation();
                event.preventDefault();
                $.fancyambox.open({
                    src: this.options.loaderUrl + '?post_url=' + postUrl,
                    type: 'ajax',
                    toolbar : false,
                    parentEl: itemsCont,
                    afterShow: function () {
                        window.instgrm.Embeds.process();
                    }
                });
            }
        }
    });

    return $.mage.amInstPostPopup;
});
