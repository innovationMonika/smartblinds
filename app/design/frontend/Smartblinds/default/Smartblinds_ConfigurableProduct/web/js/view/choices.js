define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/mage'
], function (
    $,
    _,
    $t
) {
    'use strict';

    var $window = $(window);

    $.widget('mage.productChoices', {
        options: {
            selectors: {
                swatchOptions: 'div[data-role="swatch-options"]',
                choicesItems: '[data-role="choices-items"]',
                topProductOptions: '.product-option.top',
                bottomProductOptions: '.product-option.bottom',
                swatchAttribute: '.swatch-attribute'
            }
        },

        _create: function () {
            this.$choicesItems = $(this.options.selectors.choicesItems);
            this._rebuildChoicesItems();
            $window.on('updatePrice', this._rebuildChoicesItems.bind(this));
        },

        _rebuildChoicesItems: function () {
            var html = '',
                system = [],
                self = this,
                optionsConfig = [
                    {
                        selector: this.options.selectors.topProductOptions,
                        labelSelector: '.label',
                        valueSelector: '#value'
                    },
                    {
                        selector: this.options.selectors.swatchAttribute,
                        labelSelector: '.swatch-attribute-label',
                        valueSelector: '.swatch-attribute-selected-option'
                    },
                    {
                        selector: this.options.selectors.bottomProductOptions,
                        labelSelector: '.label',
                        valueSelector: '#value'
                    },
                ];
            _.each(optionsConfig, function (optionConfig) {
                $(optionConfig.selector).each(function () {
                    var $element = $(this),
                        label = $element.find(optionConfig.labelSelector).text(),
                        value = $element.find(optionConfig.valueSelector).text().trim();
                    if (!value) {
                        return;
                    }
                    //removed 'control_type'
                    if (_.contains(['system_type', 'system_size'], $element.data('attribute-code'))) {
                        system.push(value);
                        return;
                    }
                    if ($element.data('attribute-code') === 'system_color') {
                        html += '%SYSTEM_PLACEHOLDER_SUMMARY%';
                    }
                    if ($element.data('attribute-code') === 'control_type') {
                        return;
                    }
                    html += self._getItemHtml(label, value);
                })
            });
            if (system.length) {
                html = html.replace("%SYSTEM_PLACEHOLDER_SUMMARY%", self._getItemHtml($t('System:'), system.reverse().join(', ')));
            }
            this.$choicesItems.html(html);
        },

        _getItemHtml: function (label, text) {
            return '' +
                '<div class="choice-item">' +
                    '<span class="choice-label">' + label + '</span>' +
                    '<span class="choice-value">' + text + '</span>' +
                '</div>'
        }
    });

    return $.mage.productChoices;
});
