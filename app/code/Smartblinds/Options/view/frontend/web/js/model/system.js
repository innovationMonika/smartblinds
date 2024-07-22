define([
    'underscore'
], function (
    _
) {
    'use strict'

    return {
        init: function ($valueField) {
            this.$valueField = $valueField;
        },

        _isChainProduct: function () {
            let hash = new URLSearchParams(window.location.hash.substring(1)),
                sku = hash.get('sku') ? hash.get('sku').toLowerCase() : null;
            return (sku && sku.includes('chain'));
        },

        get: function () {
            if (!this.$valueField) {
                return null;
            }

            const jsonConfig = window.jsonConfig;
            let filterPredicate = getFilterPredicate(this.$valueField);

            if (this.$valueField.data('control_type') && parseInt(jsonConfig.systems[0].isChainCustomerGroup) === 1) {
                const controlType = this.$valueField.data('control_type');
                filterPredicate.controlType = isNumeric(controlType) ?
                    controlType : jsonConfig.systems[0].controlTypeData.options[controlType];
            } else {
                let defaultControlType = (this._isChainProduct()) ? "chain" : "motor";
                filterPredicate.controlType = jsonConfig.systems[0].controlTypeData["options"][defaultControlType];
            }

            return getSystemObject(filterPredicate);
        },

        getByType: function (type) {
            const jsonConfig = window.jsonConfig;
            let filterPredicate = getFilterPredicate(this.$valueField);
            filterPredicate.controlType = jsonConfig.systems[0].controlTypeData["options"][type];
            return getSystemObject(filterPredicate);
        },

        getCommonMinWidth: function () {
            return this.getSystemsByControlType()
                .reduce((prev, curr) => prev.minWidth < curr.minWidth ? prev : curr)
                .minWidth;
        },

        getCommonMaxWidth: function () {
            return this.getSystemsByControlType()
                .reduce((prev, curr) => prev.maxWidth > curr.maxWidth ? prev : curr)
                .maxWidth;
        },

        getSystemsByControlType: function () {
            return window.jsonConfig.systems
                .filter((system) => { return system.controlType === this.getControlType(system) })
        },

        getControlType: function (system) {
            if (this.$valueField.data('control_type') && parseInt(system.isChainCustomerGroup) === 1) {
                const controlType = this.$valueField.data('control_type');
                return isNumeric(controlType) ?
                    controlType : system.controlTypeData.options[controlType];
            } else {
                let defaultControlType = (this._isChainProduct()) ? "chain" : "motor";
                return system.controlTypeData['options'][defaultControlType];
            }
        },

        getCommonMinHeight: function () {
            return window.jsonConfig.systems
                .reduce((prev, curr) => prev.minHeight < curr.minHeight ? prev : curr)
                .minHeight;
        },

        getCommonMaxHeight: function () {
            return window.jsonConfig.systems
                .reduce((prev, curr) => prev.maxHeight > curr.maxHeight ? prev : curr)
                .maxHeight;
        },
    }

    function getFilterPredicate ($valueField) {
        const jsonConfig = window.jsonConfig;
        let filterPredicate = {};
        if (jsonConfig.systems[0].systemCategory !== 'venetian_blinds') {
            filterPredicate.systemType = $valueField.data('system_type');
        }
        if (jsonConfig.systems[0].systemCategory !== 'venetian_blinds' && jsonConfig.systems[0].systemCategory !== 'honeycomb_blinds') {
            filterPredicate.systemSize = $valueField.data('system_size');
        }
        return filterPredicate;
    }

    function getSystemObject(predicates) {
        var jsonConfig = window.jsonConfig;
        return _.find(jsonConfig.systems, predicates);
    }

    function isNumeric(value) {
        return /^-?\d+$/.test(value);
    }
});
