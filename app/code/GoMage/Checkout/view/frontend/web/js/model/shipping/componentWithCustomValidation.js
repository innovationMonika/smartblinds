define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'Magento_Ui/js/lib/validation/validator',
    'mage/validation'
], function ($, _, Component, utils, validator) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this._super();
            var messages = [
                {
                    inputName: 'firstname',
                    message: $.mage.__("Do not forget to fill in your first name")
                },
                {
                    inputName: 'lastname',
                    message: $.mage.__("Don't forget to fill in your surname")
                },
                {
                    inputName: 'postcode',
                    message: $.mage.__("Don't forget to fill in your postal code")
                },
                {
                    inputName: 'street[1]',
                    message: $.mage.__("Do not forget to fill in your house number")
                },
                {
                    inputName: 'street[0]',
                    message: $.mage.__("Don't forget to fill in your street name")
                },
                {
                    inputName: 'city',
                    message: $.mage.__("Don't forget to fill in your city of residence")
                },
                {
                    inputName: 'telephone',
                    message: $.mage.__("Don't forget to fill in your phone number")
                }
            ];

            _.each(messages, function (element){
                validator.addRule(
                    'required-entry-' + element.inputName,
                    function (value) {
                        return !utils.isEmpty(value);
                    },
                    element.message
                );
            });

            return this;
        },
    });
});
