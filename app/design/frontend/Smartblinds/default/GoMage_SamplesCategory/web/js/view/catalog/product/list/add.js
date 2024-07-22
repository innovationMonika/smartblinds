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
            this
                ._super()
                .initEvents()
                .rebindForAjaxContent()
                .updateItemState();
            return this;
        },

        initObservable: function () {
            this
                ._super()
                .observe(['buttonText', 'toggleClass']);
            return this;
        },

        initEvents: function () {
            $window.on('sample-basket-updated', this.onBaskedUpdated.bind(this));
            return this;
        },

        onBaskedUpdated: function (e) {
            this.updateItemState();
        },

        updateItemState: function () {
            let item = _.findWhere(this.items, {id: this.itemId});
            if (item && basket.getItemBySwatches(item)) {
                this.buttonText(this.addedButtonText)
                    .toggleClass(this.addedToCartClass);

            } else {
                this.buttonText(this.addButtonText)
                    .toggleClass(this.addToCartClass);
            }
            return this;
        },

        addToBasket: function () {
            let parentItem = null,
                item = _.findWhere(this.items, {id: this.itemId});
            if (!item) {
                return this;
            }
            if (basket.getItemBySwatches(item)) {
                return this;
            }
            try {
                if (item.parentId) {
                    parentItem = _.findWhere(this.items, {id: item.parentId});
                }
                basket.addItem(item, parentItem);
            } catch (e) {
                console.log(e);
            }
            return this;
        },

        removeFromBasket: function () {
            let item = _.findWhere(this.items, {id: this.itemId});
            if (!item) {
                return this;
            }

            if (basket.getItemBySwatches(item)) {
                basket.removeItemBySwatches(item);
                return this;
            }
            return this;
        },

        rebindForAjaxContent: function () {
            let $element = $(this.elementSelector),
                element = $element.get(0);
            if (!_.isUndefined(element)) {
                ko.cleanNode(element);
                ko.applyBindings(this, element);
            }
            return this;
        }
    });

});
