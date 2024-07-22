define([
    'jquery',
    'underscore',
    'mage/template'
], function ($, _, mageTemplate) {
    'use strict';

    return function (quickSearch) {
        $.widget('mage.quickSearch', quickSearch, {
            options: {
                template:
                    '<li class="<%- data.row_class %>" id="qs-option-<%- data.index %>" role="option">' +
                    '<span class="qs-option-name">' +
                    '<%- data.title %>' +
                    '</span>' +
                    '<span aria-hidden="true" class="amount">' +
                    '<%- data.num_results %>' +
                    '</span>' +
                    '</li>',
            },
            _create: function () {
                this._super();
                this.element.off('blur').on('change keydown keyup paste', function () {
                    if ($(this).val().length > 0) {
                        $(this).addClass('_filled');
                    } else {
                        $(this).removeClass('_filled');
                    }
                });
                $('[data-action="clear-search"]').on('click', function () {
                    $(this).parent().find('input').val('').removeClass('_filled').focus();
                    if ($('.search-autocomplete:visible').length) {
                        $('.search-autocomplete:visible').hide();
                    }
                });
                String.prototype.replaceAll = function (strReplace, strWith) {
                    var esc = strReplace.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    var reg = new RegExp(esc, 'ig');
                    return this.replace(reg, strWith);
                };
            },
            _onPropertyChange: function () {
                var searchField = this.element,
                    clonePosition = {
                        position: 'absolute',
                        width: searchField.outerWidth()
                    },
                    source = this.options.template,
                    template = mageTemplate(source),
                    dropdown = $('<ul role="listbox"></ul>'),
                    value = this.element.val();

                this.submitBtn.disabled = true;

                if (value.length >= parseInt(this.options.minSearchLength, 10)) {
                    this.submitBtn.disabled = false;
                    $.getJSON(this.options.url, {
                        q: value
                    }, $.proxy(function (data) {
                        if (data.length) {
                            $.each(data, function (index, element) {
                                var html;

                                element.index = index;
                                html = template({
                                    data: element
                                });
                                dropdown.append(html);
                            });

                            this._resetResponseList(true);

                            this.responseList.indexList = this.autoComplete.html(dropdown)
                                .css(clonePosition)
                                .show()
                                .find(this.options.responseFieldElements + ':visible');

                            this.autoComplete.find('.qs-option-name').each(function () {
                                let nameText = $(this).text();
                                $(this).html(nameText.replaceAll(value, '<strong>' + value + '</strong>'));
                            });

                            this.element.removeAttr('aria-activedescendant');

                            if (this.responseList.indexList.length) {
                                this._updateAriaHasPopup(true);
                            } else {
                                this._updateAriaHasPopup(false);
                            }

                            this.responseList.indexList
                                .on('click', function (e) {
                                    this.responseList.selected = $(e.currentTarget);
                                    this.searchForm.trigger('submit');
                                }.bind(this))
                                .on('mouseenter mouseleave', function (e) {
                                    this.responseList.indexList.removeClass(this.options.selectClass);
                                    $(e.target).addClass(this.options.selectClass);
                                    this.responseList.selected = $(e.target);
                                    this.element.attr('aria-activedescendant', $(e.target).attr('id'));
                                }.bind(this))
                                .on('mouseout', function (e) {
                                    if (!this._getLastElement() &&
                                        this._getLastElement().hasClass(this.options.selectClass)) {
                                        $(e.target).removeClass(this.options.selectClass);
                                        this._resetResponseList(false);
                                    }
                                }.bind(this));
                        } else {
                            this._resetResponseList(true);
                            this.autoComplete.hide();
                            this._updateAriaHasPopup(false);
                            this.element.removeAttr('aria-activedescendant');
                        }
                    }, this));
                } else {
                    this._resetResponseList(true);
                    this.autoComplete.hide();
                    this._updateAriaHasPopup(false);
                    this.element.removeAttr('aria-activedescendant');
                }
            }
        });
        return $.mage.quickSearch;
    }

});