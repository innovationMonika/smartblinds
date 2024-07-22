define([
    'Magento_Ui/js/form/element/post-code'
], function (Component) {
    'use strict';

    return Component.extend({
        update: function (value) {
            this._super();

            this.validation['required-entry-postcode'] = true;
            this.validation['required-entry'] = false;
            this.required(false);
        }
    });
});
