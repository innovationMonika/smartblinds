define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils'
], function (
    $,
    _,
    priceUtils
) {
    'use strict';

    var $window = $(window);

    var mixin = {
        processSwatchLabel: function ($selectOption)
        {
            var $select = $selectOption.parents('select');
            var optionId = priceUtils.findOptionId($select);
            var selectOptions = $('#select_' + optionId + ' option');
            if (!selectOptions) {
                return;
            }

            var optionLabel = $select.parents('.field').find('label');
            if (optionLabel.parent().find('span#value').length <= 0) {
                optionLabel.after('<span id="value"></span>');
            }

            let elOption = $('.product-option-header label[for="select_'+optionId+'"]').parent().parent();
            if(elOption.find('.control .mage-error').length > 0) {
                elOption.find('.control .mage-error').remove();
            }

            var labelText = [];
            var isSelectedOptionExist = false;
            var inArrayValue = -1;
            if ($select.val()) {
                $(selectOptions).each(function () {
                    if (Array.isArray($select.val())) {
                        inArrayValue = $.inArray($(this).attr('value'), $select.val());
                    }
                    if (inArrayValue !== -1 || $select.val() === $(this).attr('value')) {
                        isSelectedOptionExist = true;
                        var $swatch = $("[data-option-type-id='" + $(this).attr('value') + "']");
                        $swatch.addClass('selected');
                        if ($swatch.attr('data-option-price') > 0) {
                            labelText.push($(this).text());
                        } else {
                            labelText.push($swatch.attr('data-option-label'));
                        }
                    }
                });
            }
            var $el = optionLabel.parent().find('span#value');
            if (isSelectedOptionExist === false) {
                $el.html('');
            } else {
                $el.html(labelText.join(', '));
            }
        },

        _onClick: function (option)
        {
            var $option = $(option);
            if ($option.hasClass('disabled')) {
                return;
            }

            var self = this;
            var optionId = $option.attr('data-option-id');
            var optionValueId = $option.attr('data-option-type-id');
            var optionType = $option.attr('data-option-type');
            var $select = $('#select_' + optionId);
            var selectOptions = $('#select_' + optionId + ' option');
            if (!selectOptions) {
                return;
            }

            if ($option.parents('.field').find('label').parent().find('span#value').length <= 0) {
                $option.parents('.field').find('label').after('<span id="value"></span>');
            }
            $(selectOptions).each(function () {
                if ($(this).val() != optionValueId || $option.hasClass('selected')) {
                    return;
                }
                if (optionType === 'multiple') {
                    if (!$select.val()) {
                        $select.val(optionValueId)
                    } else {
                        var values = $select.val();
                        values.push(optionValueId);
                        $select.val(values);
                    }
                } else {
                    $select.val(optionValueId);
                    var $el = $option.parents('.field').find('label').parent().find('span#value');
                    $el.html($option.attr('data-option-label'));
                    if ($option.attr('data-option-price') > 0) {
                        $el.html($el.html() + ' +' + priceUtils.formatPrice($option.attr('data-option-price')));
                    }
                    $option.parent().parent().find('.selected').removeClass('selected');
                }
                $option.addClass('selected');
                $select.trigger('change');
            });

            $window.trigger('options-click', $option);
        }
    };

    return function (targetWidget) {
        $.widget('mageworx.optionSwatches', targetWidget, mixin);
        return $.mageworx.optionSwatches;
    };
});
