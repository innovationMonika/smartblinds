define([
    'underscore'
], function (
    _
) {
    'use strict';

    function collectAllCombinations(mappedAttributes) {
        var level = _.keys(mappedAttributes).length;
        var attributeValues = _.values(mappedAttributes);
        var combinations = [];
        for (var i = level - 1; i >= 0; i--) {
            var attribute = attributeValues[i];
            var pairs = _.map(attribute.options, function (option) {
                return [attribute.code, option.id];
            });
            if (i === level - 1) {
                combinations = _.map(pairs, function (pair) {
                    return [pair];
                });
                continue;
            }
            var newCombinations = [];
            _.each(pairs, function (pair) {
                var combos = _.map(combinations, function (combination) {
                    var combo = Array.from(combination);
                    combo.push(pair);
                    return combo;
                });
                newCombinations = _.union(newCombinations, combos);
            });
            combinations = newCombinations;
        }
        return _.map(combinations, function (combination) {
            return _.object(combination);
        });
    }

    function isCombinationAvailable(mappedAttributes, combination) {
        var products = undefined;
        _.each(combination, function (optionId, attributeCode) {
            var attribute =  _.findWhere(mappedAttributes, {code: attributeCode}),
                option = attribute ? _.findWhere(attribute.options, {id: optionId}) : undefined;
            if (_.isUndefined(products)) {
                products = option.products;
            } else {
                products = _.intersection(products, option.products);
            }
        });
        return Boolean(products.length);
    }

    return function (mappedAttributes) {
        var combinations = collectAllCombinations(mappedAttributes);
        for (var i = 0; i < combinations.length; i++) {
            var combination = combinations[i];
            if (isCombinationAvailable(mappedAttributes, combination)) {
                var a = 1;
                return combination;
            }
        }
        return [];
    };
});
