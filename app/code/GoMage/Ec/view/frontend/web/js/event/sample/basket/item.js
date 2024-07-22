define([
    'jquery',
    'underscore'
], function(
    $,
    _
) {
    'use strict';

    var sampleBasketItemsEvent = {

        $window: $(window),

        addEvent: function (event) {
            let item = event.detail.item;
            if( 'undefined' !== item && 'undefined' !== typeof AEC && 'undefined' !== typeof dataLayer) {
                dataLayer.push(
                {
                    'event': 'AddSample',
                    'eventCategory': 'AddtoCart',
                    'eventAction': 'Sample',
                    'eventLabel' : item.name,
                    'ecommerce':
                    {
                        'currencyCode': AEC.currencyCode,
                    }
                });
            }
        },

        initEvents: function () {
            this.$window.on('sample-item-add', this.addEvent.bind(this));
            return this;
        },

        init: function () {
            this.initEvents();
        }

    }

    sampleBasketItemsEvent.init();

});
