define([
    'uiComponent'
], function(
    Component
) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this.checkSticky();
        },

        checkSticky: function () {
            const stickyElm = document.querySelector('#amasty-shopby-product-list .sticky-action');
            const observer = new IntersectionObserver( 
              ([e]) => e.target.classList.toggle('isSticky', e.intersectionRatio < 1),
              {threshold: [1]}
            );

            observer.observe(stickyElm);
        }

    });

});
