define([
    'jquery',
    'optionSwatches',
    'uiRegistry'
], function($, optionSwatches, registry) {
    'use strict';

    return function(config) {
        var optionBase = registry.get('mageworxOptionBase');
        if (optionBase) {
            optionBase.addUpdater(20, optionSwatches(config.jsonData));
        } else {
            var updaters = registry.get('mageworxOptionUpdaters');
            if (!updaters) {
                updaters = {};
            }
            updaters[20] = optionSwatches(config.jsonData);
            registry.set('mageworxOptionUpdaters', updaters);
        }
    }

});
