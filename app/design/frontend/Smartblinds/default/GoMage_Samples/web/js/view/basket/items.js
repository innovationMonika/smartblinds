define([
    'underscore',
    'jquery',
    'mage/translate',
    'uiComponent',
    'sampleBasket',
    'knockout',
    'domReady!'
], function(
    _,
    $,
    $t,
    Component,
    basket,
    ko
) {
    'use strict';

    var $window = $(window);

    return Component.extend({
        defaults: {
            links: {
                successMessage: 'basket-form:successMessage'
            }
        },

        initialize: function () {
            this._super()
                .initVariables()
                .initEvents();
            return this;
        },

        initObservable: function () {
            return this
                ._super()
                .observe([
                    'items',
                    'successMessage'
                ].join(' '));
        },

        initVariables: function () {
            this.updateBasketItems();
            this.itemGroups = ko.computed(this.buildItemGroups.bind(this));
            return this;
        },

        initEvents: function () {
            $window.on('sample-basket-updated', this.updateBasketItems.bind(this));
            return this;
        },

        updateBasketItems: function () {
            this.items(basket.getSimpleItems());
        },

        buildItemGroups: function () {
            var systemGroups = {};
            _.each(this.items(), function (item) {
                var systemCategory = item.systemCategory;
                var systemGroupKey = systemCategory;
                if (systemCategory === 'honeycomb_blinds' && item?.transparency) {
                    systemGroupKey = systemCategory + '_' + item?.transparency.replace(/\s/g, '').toLowerCase()
                }
                var systemGroup = systemGroups.hasOwnProperty(systemGroupKey) ?
                    systemGroups[systemGroupKey] : null;

                if (!systemGroup) {
                    let groupLabel = this._getSystemLabel(item)
                    systemGroup = {
                        label: groupLabel,
                        groups: {},
                        positions: []
                    }
                    systemGroups[systemGroupKey] = systemGroup;
                }
                var position = {
                    id: item.id,
                    label: item.name /*+ this._getTee(item, systemGroup)*/,
                    image: item.swatches.color.imageUrl,
                    url: item.url
                };
                if (['honeycomb_blinds', 'duoroller', 'venetian_blinds'].includes(systemCategory)) {
                    systemGroup.positions.push(position);
                    return;
                }
                var transparencyOptionId = item.swatches.transparency.optionId;
                var group = systemGroup.groups.hasOwnProperty(transparencyOptionId) ?
                    systemGroup.groups[transparencyOptionId] : null;
                if (!group) {
                    group = {
                        label: this._getTranslatedTransparencyLabel(item.swatches.transparency.optionLabel),
                        positions: []
                    }
                    systemGroup.groups[transparencyOptionId] = group;
                }
                systemGroup.groups[transparencyOptionId].positions.push(position);
            }, this);
            _.each(systemGroups, function (systemGroup) {
                systemGroup.groups = _.values(systemGroup.groups);
            })

            //return _.values(systemGroups);
            return this._updateNames(systemGroups);
        },

        removeItem: function (item) {
            basket.removeItem(item.id);
        },

        _getSystemLabel(item) {
            switch (item?.systemCategory) {
                case 'duoroller':
                    return $t('Duo Rollerblind');
                case 'venetian_blinds':
                    return $t('Venetian blind');
                case 'honeycomb_blinds':
                    switch (item?.transparency) {
                        case 'Lightfiltering':
                            return $t('Lightfiltering Honeycomb blind');
                        case 'Obscuring':
                            return $t('Obscuring Honeycomb blind');
                        default:
                            return $t('Honeycomb blind');
                    }
                    return $t('Honeycomb blind');
                default:
                    return $t('Rollerblind');
            }
        },

        _getTranslatedTransparencyLabel(optionLabel) {
            var obj = {
                'Lightfiltering': $t('Lightfiltering'),
                'Obscuring': $t('Obscuring'),
                'Translucent' : $t('Translucent')
            }
            return obj.hasOwnProperty(optionLabel) ? obj[optionLabel] : optionLabel;
        },

        _getTee(item, systemGroup) {
            /*if (item.systemCategory === 'duoroller') {
                return ' - ' + systemGroup.label;
            } else {
                var transparencyOptionId = item.swatches.transparency.optionId;
                var group = systemGroup.groups.hasOwnProperty(transparencyOptionId) ? systemGroup.groups[transparencyOptionId] : null;
                if (!group) {
                    group = {
                        label: this._getTranslatedTransparencyLabel(item.swatches.transparency.optionLabel)
                    }

                    return ' - ' + group.label + ' ' + systemGroup.label;
                }
            }*/
        },

        _updateNames(systemGroups) {
            _.each(_.values(systemGroups), function (item) {
                if (item.groups.length) {
                    _.each(item.groups, function (group) {
                        if (group.positions.length) {
                            _.each(group.positions, function (element) {
                                element.name = element.label + ' - ' + group.label + ' ' + item.label;
                            });
                        }
                    });
                } else  {
                    if (item.positions.length) {
                        _.each(item.positions, function (element) {
                            element.name = element.label + ' - ' + item.label;
                        });
                    }
                }
            });

            return _.values(systemGroups);
        }

    });

});
