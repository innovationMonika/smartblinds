define([
    'jquery',
    'mage/translate',
    'uiComponent',
    'sampleBasket',
    'Magento_Ui/js/modal/modal',
], function(
    $,
    $t,
    Component,
    basket,
    modal
) {
    'use strict';

    var $window = $(window);

    return Component.extend({
        defaults: {
            modalOptions: {
                type: 'custom',
                responsive: true,
                modalClass: 'samples-basket',
                title: $t('Basket')
            },
            links: {
                successMessage: 'basket-form:successMessage'
            }
        },

        initialize: function () {
            this._super()
                .initElements()
                .initVariables()
                .initEvents();

            this.trySwitchCounterVisibility();

            return this;
        },

        initObservable: function () {
            return this
                ._super()
                .observe([
                    'items',
                    'successMessage'
                ].join(' '));
        },

        initElements: function () {
            this.$modal = $(this.selectors.modal);
            return this;
        },

        initVariables: function () {
            this.updateBasketItems();
            return this;
        },

        initEvents: function () {
            $window.on('sample-basket-updated', this.onItemsUpdated.bind(this));
            this.$modal.on('modalclosed', this.onCloseModal.bind(this));
            return this;
        },

        onItemsUpdated: function () {
            this.updateBasketItems();
            this.trySwitchCounterVisibility();
        },

        updateBasketItems: function () {
            this.items(basket.getSimpleItems());
        },

        trySwitchCounterVisibility: function () {
            if (this.items().length) {
                $(this.selectors.counter).show();
            }
        },

        toggleModal: function () {
            modal(this.modalOptions, this.$modal);
            this.$modal.modal('openModal');
        },

        onCloseModal: function () {
            this.successMessage(null);
        }

    });

});
