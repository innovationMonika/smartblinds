/**
 * Missing Prototype library fix
 */
define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
    'prototype'
], function (dynamicRowsGrid) {
    'use strict';

    return dynamicRowsGrid.extend({
        elementForRemove: null,

        processingInsertData: function (data) {
            var changes,
                obj = {};

            changes = this._checkGridData(data);
            this.cacheGridData = data;


            if (changes.length) {
                if (this.elementForRemove == changes[0][this.map[this.identificationProperty]]) {
                    this.elementForRemove = null;
                    this.deleteRecord(0, changes[0][this.map[this.identificationProperty]]);
                    return false;
                }
                obj[this.identificationDRProperty] = changes[0][this.map[this.identificationProperty]];

                if (_.findWhere(this.recordData(), obj)) {
                    return false;
                }

                changes.forEach(function (changedObject) {
                    this.mappingValue(changedObject);
                }, this);

                if (data.length > 1) {
                    this.elementForRemove = data[1][this.identificationDRProperty];
                    this.cacheGridData = changes;
                }
            }
        },
    });
});
