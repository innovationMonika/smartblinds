define([
    'Magento_Ui/js/grid/columns/multiselect'
], function (Multiselect) {
    'use strict';

    return Multiselect.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            fieldClass: {
                'data-grid-onoff-cell': true,
                'data-grid-checkbox-cell': false
            },
        },

        deselectAll: function () {
            this.excludeMode(false);

            this.clearExcluded();
            this.selected.remove();

            return this;
        },

        countSelected: function () {
            var total = this.totalRecords(),
                excluded = this.excluded().length,
                selected = this.selected().length ? 1 : 0;

            if (this.excludeMode()) {
                selected = total - excluded;
            }

            this.totalSelected(selected);

            return this;
        }
    });
});
