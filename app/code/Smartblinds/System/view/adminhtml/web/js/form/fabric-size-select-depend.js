define([
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
], function (registry, Select) {
    'use strict';

    return Select.extend({
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
            if (value === 'honeycomb_blinds') {
                this.setVisible(true);
            } else {
                this.setVisible(false);
            }
        }
    });
});
