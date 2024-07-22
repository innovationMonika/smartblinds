define([
    'underscore',
    'jquery'
], function (
    _,
    $
) {
    'use strict';

    return function (jsonConfig, $widget) {
        if ($widget.isSelectionEmulated) {
            return;
        }

        var selectedOptions = '.' + $widget.options.classes.attributeClass + '[data-option-selected]';
        var selectedAttributes = _.object(_.map($widget.element.find(selectedOptions), function (selectedOption) {
            var $selectedOption = $(selectedOption);
            return [$selectedOption.data('attribute-code'), $selectedOption.attr('data-option-selected')]
        }));

        var products = _.filter(_.map(selectedAttributes, function (optionId, attributeCode) {
            var attribute =  _.findWhere(jsonConfig.mappedAttributes, {code: attributeCode}),
                option = attribute ? _.findWhere(attribute.options, {id: optionId}) : undefined;
            return option ? option.products : null;
        }));

        var productId = _.intersection.apply(_, products)[0],
            sku = productId ? jsonConfig.sku[productId] : null;

        if (sku) {
            history.replaceState(null, null, '#sku=' + sku);
        }
    };

});
