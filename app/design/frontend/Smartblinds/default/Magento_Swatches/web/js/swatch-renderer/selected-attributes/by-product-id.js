define([
    'underscore'
], function (
    _
) {
    'use strict';

    return function (mappedAttributes, productId) {
        return _.object(_.filter(_.map(mappedAttributes, function (attribute) {
            var selectableOption = _.find(attribute.options, function (option) {
                return _.contains(option.products, productId);
            })
            return selectableOption ? [attribute.code, selectableOption.id] : null;
        })));
    };

});
