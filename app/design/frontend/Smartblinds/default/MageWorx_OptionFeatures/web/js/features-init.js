define([
    'jquery',
    'optionFeatures',
    'optionFeaturesIsDefault',
    'uiRegistry'
], function($, optionFeatures, optionFeaturesIsDefault, registry) {
    'use strict';

    return function(config) {
        var optionBase = registry.get('mageworxOptionBase');
        if (optionBase) {
            optionBase.addUpdater(10, optionFeatures(config.jsonData));
            optionBase.addUpdater(50, optionFeaturesIsDefault(config.defaultJsonData));
        } else {
            var updaters = registry.get('mageworxOptionUpdaters');
            if (!updaters) {
                updaters = {};
            }
            updaters[10] = optionFeatures(config.jsonData);
            updaters[50] = optionFeaturesIsDefault(config.defaultJsonData);
            registry.set('mageworxOptionUpdaters', updaters);
        }
        var $mageWorxSwatchOption = $('.product-custom-option');
        if ($mageWorxSwatchOption.length && optionBase) {
            optionBase.optionChanged({target: $mageWorxSwatchOption.get(0)});
        }
    }

});
