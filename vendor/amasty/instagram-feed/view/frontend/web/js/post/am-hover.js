define([
    'jquery',
], function ($) {
    'use strict';

    $.widget('mage.amInstHover', {
        options: {
            hoverClass: '-hovered',
            postSelector: '[data-aminst-hover="true"]',
            relationLinkSelector: '[data-aminst-js="relationLink"]',
            feedLinkSelector: '[data-aminst-js="feed-link"]'
        },

        /**
         * Initialize widget, adding _hover() to posts with relation link
         * @private
         */
        _create: function () {
            this.posts = this.element.find(this.options.postSelector);
            this.relationLinks = this.posts.find(this.options.relationLinkSelector);

            this._hover(this.posts);
            this._hover(this.relationLinks);

            this._loadMoreObserver(this.element[0]);
        },

        /**
         * Observing the posts container and watch for new posts with relation link, adding _hover() to them
         * @private
         */
        _loadMoreObserver: function (container) {
            var self = this,
                options = { childList: true },
                observer = new MutationObserver(mCallback);

            function mCallback(mutations) {
                mutations.forEach(function (mutation) {
                    if (!mutation.addedNodes.length) {
                       return;
                    }

                    if (mutation.type === 'childList' && mutation.addedNodes[0].dataset.aminstHover) {
                        self._hover($(mutation.addedNodes[0]));
                        self._hover($(mutation.addedNodes[0]).find(self.options.relationLinkSelector));
                    }
                });
            }

            observer.observe(container, options);
        },

        /**
         * Listener to resolve double hover
         * @private
         */
        _hover: function (target) {
            var self = this,
                options = self.options;

            target.hover(function (e) {
                if ($(this).context.dataset.aminstHover
                  && e.target.className === 'aminst-feed-comment-cont'
                  || e.target.className === 'aminst-feed-img') {
                    $(this).children(options.feedLinkSelector).addClass(options.hoverClass);
                }
            }, function (e) {
                if (e.relatedTarget === null) {
                    return;
                }

                if ($(this).context.dataset.aminstHover) {
                    $(this).children(options.feedLinkSelector).removeClass(options.hoverClass);
                }

                if ($(this).context.dataset.aminstJs === 'relationLink'
                  && e.relatedTarget.className === 'aminst-feed-comment-cont'
                  || e.relatedTarget.className === 'aminst-feed-img') {
                    $(this).prev(options.feedLinkSelector).addClass(options.hoverClass);
                }
            });
        }
    });

    return $.mage.amInstHover;
});
