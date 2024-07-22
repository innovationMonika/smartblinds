define([], function () {
    'use strict';

    const mixin = {
        ifShowValue: function () {
            if (window.checkoutConfig.isHideTax) {
                return false;
            }
            return this._super();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});

