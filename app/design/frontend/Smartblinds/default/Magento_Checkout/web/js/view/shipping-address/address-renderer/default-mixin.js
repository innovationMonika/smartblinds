define([], function () {
    'use strict';

    var mixin = {
        preSelectAddress: function () {
            if (this.address().isDefaultShipping()) {
                this.selectAddress();
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
