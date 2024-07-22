define(['jquery'], function ($) {
    'use strict';

    const mixin = {
        options: {
            errorPlacement: function (error, element) {

                const isWarning = error && error.get(0) && error.get(0).innerText.includes('Warning');
                const isWidth = element.attr('data-role') === 'width';
                const isHeight = element.attr('data-role') === 'height';
                const isCurtainTrackWidth = element.attr('data-role') === 'curtain_tracks_width';

                if (!isWarning && (isWidth || isHeight || isCurtainTrackWidth)) {
                    $('#error-placement-element').html(error);
                    return;
                }

                if (this._super) {
                    return this._super();
                }
            }
        }
    };

    return function (targetWidget) {
        $.widget('mage.validation', targetWidget, mixin);
        return $.mage.validation;
    };
});
