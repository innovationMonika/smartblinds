define([
    'underscore'
], function (
    _
) {
    'use strict';

    return function (mappedAttributes, selectedAttributes) {
        return _.filter(selectedAttributes, function (optionId, attributeCode) {
            var attribute =  _.findWhere(mappedAttributes, {code: attributeCode}),
                option = attribute ? _.findWhere(attribute.options, {id: optionId}) : undefined;
            return !_.isUndefined(option);
        }).length;
    };

});
