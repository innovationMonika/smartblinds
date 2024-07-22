define([
    'jquery',
    'ko',
    'uiComponent',
    'GoMage_Checkout/js/view/form/element/email'
], function ($, ko, Component, emailComponent) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'GoMage_Checkout/form/element/create_account',
                isChecked: false,
            },
            passwordSelector: '.create-account-password',
            checkboxSelector: '.checkout-create-account',

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super()
                    .observe('isChecked');

                var self = this;
                this.isChecked.subscribe(function (value) {
                    if (value) {
                        $(self.passwordSelector).show()
                    } else {
                        $(self.passwordSelector).hide()
                    }
                }.bind(this));

                return this;
            },

            /**
             * @override
             */
            initialize: function () {
                this._super();
                if (emailComponent().isPasswordVisible()) {
                    $(this.checkboxSelector).hide();
                } else {
                    $(this.checkboxSelector).show();
                }
            }
        });
    }
);
