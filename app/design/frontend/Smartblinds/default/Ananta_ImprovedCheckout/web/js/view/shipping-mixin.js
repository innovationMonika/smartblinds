define(function () {
    'use strict';
    var self = {};

    var mixin = {

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
            self = this;
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            self.isFormPopUpVisible(true);
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
