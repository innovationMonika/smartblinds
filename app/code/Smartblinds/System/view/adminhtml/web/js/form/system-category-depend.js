define([
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
], function (registry, AbstractElement) {
    'use strict';

    return AbstractElement.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.system_category:value'
            }
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            if (value === 'venetian_blinds' || value === 'honeycomb_blinds') {
                this.setVisible(false);
            } else {
                this.setVisible(true);
            }
        }
    });
});

