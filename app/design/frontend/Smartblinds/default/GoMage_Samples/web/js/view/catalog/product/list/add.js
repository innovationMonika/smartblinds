define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'sampleBasket'
], function(
    $,
    _,
    Component,
    ko,
    basket
) {
    'use strict';

    var $window = $(window);

    return Component.extend({
        initialize: function () {
            this._super()
                .initEvents();
            this.updateStateIcon();
            this.rebindForAjaxContent();
            return this;
        },

        initObservable: function () {
            this._super()
                .observe(['stateIcon', 'changeClass']);
            return this;
        },

        initEvents: function () {
            $window.on('sample-basket-updated', this.onBaskedUpdated.bind(this));
            return this;
        },

        onBaskedUpdated: function (e) {
            this.updateStateIcon();
        },

        updateStateIcon: function () {
            var item = _.findWhere(this.items, {id: this.itemId});
            if (item && basket.getItemBySwatches(item)) {
                this.stateIcon(this.addedToCartIcon);
                this.changeClass(this.addedToCartClass);
            } else {
                this.stateIcon(this.addToCartIcon);
                this.changeClass(this.addToCartClass);
            }
        },

        updateBasket: function () {
            var item = _.findWhere(this.items, {id: this.itemId});
            if (!item) {
                return;
            }
            if (basket.getItemBySwatches(item)) {
                basket.removeItemBySwatches(item);
                return;
            }
            try {
                var parentItem = null;
                if (item.parentId) {
                    parentItem = _.findWhere(this.items, {id: item.parentId});
                }
                basket.addItem(item, parentItem);
            } catch (e) {
                alert(e);
            }
        },

        rebindForAjaxContent: function () {
            var $element = $(this.elementSelector),
                element = $element.get(0);
            if (!_.isUndefined(element)) {
                ko.cleanNode(element);
                ko.applyBindings(this, element);
            }
        }
    });

});
