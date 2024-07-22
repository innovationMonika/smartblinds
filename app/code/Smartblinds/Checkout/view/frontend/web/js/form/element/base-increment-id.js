define([
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
], function (registry, AbstractElement) {
    'use strict';

    return AbstractElement.extend({
        defaults: {
            imports: {
                onOrderTypeValueChanged: '${ $.parentName }.order_type:value'
            }
        },

        onOrderTypeValueChanged: function (value) {
            if (value === 'WARRANTY') {
                this.setVisible(true);
            } else {
                this.setVisible(false);
            }
        }
    });
});

