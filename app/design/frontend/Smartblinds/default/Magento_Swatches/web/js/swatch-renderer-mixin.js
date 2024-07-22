define([
    'jquery',
    'underscore',
    'Magento_Swatches/js/swatch-renderer/selected-attributes/by-product-id',
    'Magento_Swatches/js/swatch-renderer/selected-attributes/validate',
    'Magento_Swatches/js/swatch-renderer/selected-attributes/first-available',
    'Magento_Swatches/js/swatch-renderer/selected-attributes/update-url',
    'getSwatchSelectedProductId'
], function (
    $,
    _,
    getSelectedAttributesByProductId,
    validateSelectedAttributes,
    getDefaultSelectedAttributes,
    updateSelectedAttributesUrl,
    getSwatchSelectedProductId
) {
    'use strict';

    const
        $window = $(window),
        mobileWidth = 767

    let galleryLoaded = false;
    let lazyLoaded = false;

    var swatchRendererMixin = {
        _create: function () {
            this._super();
            this._setupResizeEventForGallery();
            this._onClickEventForMobileGallery();
            this._onClickEventForMobileChoicesGallery();
        },

        _setupResizeEventForGallery: function () {
            if ($window.width() > mobileWidth) {
                return;
            }
            $window.on('resize', () => {
                if (!galleryLoaded && $window.width() > mobileWidth) {
                    this._LoadProductMedia();
                    galleryLoaded = true;
                }
            });
        },

        _onClickEventForMobileGallery: function () {
            var self = this;
            $('.js-product-media-swiper').on('click', '.swiper-slide', function () {
                if (!galleryLoaded) {
                    self._LoadProductMedia();
                    galleryLoaded = true;
                }
            });
        },

        _onClickEventForMobileChoicesGallery: function () {
            var self = this;
            $('.choices-swiper').on('click', '.swiper-slide', function () {
                if (!galleryLoaded) {
                    self._LoadProductMedia();
                    galleryLoaded = true;
                }
            });
        },

        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.data('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if(!$input.attr('data-attr-name')) {
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
            }

            if ($this.hasClass('selected')) {
                return;
            }

            this._baseOnClick($this, $widget);
            updateSelectedAttributesUrl(this.options.jsonConfig, $widget);
            $window.trigger('swatches-click', [$this, this.isSelectionEmulated]);

            const isEmulatedAndSelected = this.isSelectionEmulated && getSwatchSelectedProductId();
            if (isEmulatedAndSelected) {
                new LazyLoad({
                    elements_selector: 'img,div',
                    data_srcset: 'originalset'
                });
            }
            if (!this.isSelectionEmulated || isEmulatedAndSelected) {
                this._updateAlternateSwatches();
                this._updateDeliveryTermsMessage();
            }
        },

        _baseOnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.data('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput),
                checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']),
                $priceBox = $widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('data-option-selected', $this.data('option-id')).find('.selected').removeClass('selected');
                $label.text($this.data('option-label'));
                $input.val($this.data('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                $widget._toggleCheckedAttributes($this, $wrapper);
            }

            if (!this.isSelectionEmulated) {
                $widget._Rebuild();

                if ($priceBox.is(':data(mage-priceBox)')) {
                    $widget._UpdatePrice();
                }

                $(document).trigger('updateMsrpPriceBlock',
                    [
                        this._getSelectedOptionPriceIndex(),
                        $widget.options.jsonConfig.optionPrices,
                        $priceBox
                    ]);

                if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                    $widget._loadMedia();
                }
            }

            $input.trigger('change');
        },

        _UpdatePrice: function () {
            if (!this.isSelectionEmulated || (this.isSelectionEmulated && getSwatchSelectedProductId())) {
                this._super();
            }
        },

        updateBaseImage: function (images, context, isInProductView) {
            if (!galleryLoaded && $window.width() <= mobileWidth) {
                return;
            }
            if (!this.isSelectionEmulated || getSwatchSelectedProductId()) {
                this._super(images, context, isInProductView);
            }
        },

        _onGalleryLoaded: function (element) {
            if (galleryLoaded || $window.width() > mobileWidth) {
                this._super(element);
            }
        },

        _getSelectedAttributes: function () {
            var mappedAttributes = this.options.jsonConfig.mappedAttributes,
                hashIndex = window.location.href.indexOf('#'),
                params = hashIndex !== -1 ? $.parseQuery(window.location.href.substr(hashIndex + 1)) : null,
                productId = params ? _.invert(this.options.jsonConfig.sku)[params.sku] : null,
                attributes = productId ? getSelectedAttributesByProductId(mappedAttributes, productId) : [];

            if (!validateSelectedAttributes(mappedAttributes, attributes)) {
                return getDefaultSelectedAttributes(mappedAttributes);
            }

            return attributes;
        },

        _EmulateSelected: function (selectedAttributes) {
            this.isSelectionEmulated = true;
            const attributesCount = Object.keys(selectedAttributes).length;
            let currentAttribute = 0;
            $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                currentAttribute++;
                if (currentAttribute === attributesCount) {
                    this.isSelectionEmulated = false;
                }
                var elem = this.element.find('.' + this.options.classes.attributeClass +
                    '[data-attribute-code="' + attributeCode + '"] [data-option-id="' + optionId + '"]'),
                    parentInput = elem.parent();

                if (elem.hasClass('selected')) {
                    return;
                }

                if (parentInput.hasClass(this.options.classes.selectClass)) {
                    parentInput.val(optionId);
                    parentInput.trigger('change');
                } else {
                    elem.trigger('click');
                }
            }, this));
        },

        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText,
                isShowControlType = this.options.isShowControlType,
                showTooltip = this.options.showTooltip;

            $widget.optionsMap = {};
            var jsonConfig = this.options.jsonConfig;

            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    controlLabelId = 'option-label-' + item.code + '-' + item.id,
                    options = $widget._RenderSwatchOptions(item, controlLabelId),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item),
                    listLabel = '',
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label += '<div class="swatch-attribute-header">' +
                        '<span id="' + controlLabelId + '" class="' + classes.attributeLabelClass + '">' +
                        $('<i></i>').text(item.label).html() + ':' +
                        '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>' +
                        '<span class="product-option-info-icon" data-option-modal="product_option_modal_' + item.code + '"><span class="fas fa-info-circle"></span></span>' +
                        '</div>';
                }

                if ($widget.inProductList) {
                    $widget.productForm.append(input);
                    input = '';
                    listLabel = 'aria-label="' + $('<i></i>').text(item.label).html() + '"';
                } else {
                    listLabel = 'aria-labelledby="' + controlLabelId + '"';
                }

                var swatchesAdditionalHtml = jsonConfig.swatchesAdditionalHtml,
                    swatchAttribute = swatchesAdditionalHtml ? swatchesAdditionalHtml.swatchAttribute : null,
                    additionalHtml = swatchAttribute ? swatchAttribute[item.code] : null;

                const displayHtml = $('body').hasClass('checkout-cart-configure') ? '' : 'style="display: none';

                // Create new control
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code + ' ' + item.code + '-visible-' + isShowControlType + '" ' +
                    'data-attribute-code="' + item.code + '" ' +
                    'data-attribute-id="' + item.id + '" ' + displayHtml + '" data-role="step">' +
                    label +
                    '<div aria-activedescendant="" ' +
                    'tabindex="0" ' +
                    'aria-invalid="false" ' +
                    'aria-required="true" ' +
                    'role="listbox" ' + listLabel +
                    'class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                    options + select +
                    '</div>' + input + (additionalHtml ? additionalHtml : '') +
                    '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });

            if (showTooltip === 1) {
                // Connect Tooltip
                container
                    .find('[data-option-type="1"], [data-option-type="2"],' +
                        ' [data-option-type="0"], [data-option-type="3"]')
                    .SwatchRendererTooltip();
            }

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            //Emulate click on all swatches from Request
            $widget._EmulateSelected($.parseQuery());
            $widget._EmulateSelected($widget._getSelectedAttributes());
        },

        _RenderSwatchOptions: function (config, controlId) {
            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                sizeConfig = this.options.jsonSwatchImageSizeConfig,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                isShowControlType = this.options.isShowControlType,
                countAttributes = 0,
                html = '';
            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            var self = this;
            $.each(config.options, function (index) {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    width,
                    height,
                    attr,
                    swatchImageWidth,
                    swatchImageHeight;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                }

                id = this.id;
                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ?
                    $('<i></i>').text(optionConfig[id].value).html() : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                width = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.width : 110;
                height = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.height : 90;
                label = this.label ? $('<i></i>').text(this.label).html() : '';
                attr =
                    ' id="' + controlId + '-item-' + id + '"' +
                    ' index="' + index + '"' +
                    ' aria-checked="false"' +
                    ' aria-describedby="' + controlId + '"' +
                    ' tabindex="0"' +
                    ' data-option-type="' + type + '"' +
                    ' data-option-id="' + id + '"' +
                    ' data-option-label="' + label + '"' +
                    ' aria-label="' + label + '"' +
                    ' role="option"' +
                    ' data-thumb-width="' + width + '"' +
                    ' data-thumb-height="' + height + '"';

                attr += thumb !== '' ? ' data-option-tooltip-thumb="' + thumb + '"' : '';
                attr += value !== '' ? ' data-option-tooltip-value="' + value + '"' : '';

                var isLarge = optionConfig[id].hasOwnProperty('size_code') ? optionConfig[id]['size_code'] : false,
                    sizeCode = isLarge ? 'swatchImageLarge' : 'swatchImage';

                swatchImageWidth = _.has(sizeConfig, sizeCode) ? sizeConfig[sizeCode].width : 30;
                swatchImageHeight = _.has(sizeConfig, sizeCode) ? sizeConfig[sizeCode].height : 20;

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' data-option-empty="true"';
                }

                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        ' style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 2) {
                    // Image
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        ' style="height: initial">'
                        + '<div style="width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px"><img src="' +
                        self.options.lazyLoadPixelUrl + '" data-original="' + value + '" width="' +
                        swatchImageWidth + '" height="' + swatchImageHeight + '" /></div>' +
                        (isLarge ? '<div class="swatch-option-label">' + label + '</div>' : '') +
                        '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                } else {
                    // Default
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        },

        _updateDeliveryTermsMessage: function () {
            let deliveryTermsMessageSelector = '.product-addtocart-top-block .delivery-message, .product-info-messages .delivery-message',
                deliveryTermsMessageContainer = $(deliveryTermsMessageSelector),
                additionalAttributes = this.options.jsonConfig.additionalAttributes,
                deliveryTermsValue = additionalAttributes[this.getProduct()].delivery_terms
                    ? additionalAttributes[this.getProduct()].delivery_terms : '&nbsp;';

            if (deliveryTermsMessageContainer.length) {
                $(deliveryTermsMessageContainer).html(deliveryTermsValue);
            }
        },

        _updateAlternateSwatches: function () {
            var systemTypeOptionId = $('.swatch-attribute.system_type')
                .find('.swatch-option.selected')
                .data('option-id');
            if (!systemTypeOptionId) {
                this._returnOriginalSwatches();
                return;
            }
            this._updateAlternateSystemTypes();
            this._updateOtherAlternateSwatches(systemTypeOptionId);
        },

        _updateAlternateSystemTypes: function () {
            _.each(this.options.jsonConfig.alternateSwatches.mapping, function (map, typeOptionId) {
                var alternateType = map[typeOptionId];
                var alternateOption = this.options.jsonConfig.alternateSwatches.options.hasOwnProperty(alternateType) ?
                    this.options.jsonConfig.alternateSwatches.options[alternateType] : null;
                if (!alternateOption) {
                    return;
                }
                this._replaceSwatchImage(typeOptionId, alternateOption);
            }, this);
        },

        _updateOtherAlternateSwatches: function (systemTypeOptionId) {
            var mapObj = this.options.jsonConfig.alternateSwatches.mapping.hasOwnProperty(systemTypeOptionId) ?
                this.options.jsonConfig.alternateSwatches.mapping[systemTypeOptionId] : null;
            if (!mapObj) {
                this._returnOriginalSwatches();
                return;
            }
            _.each(mapObj, function (alternateOptionId, optionId) {
                var alternateOption = this.options.jsonConfig.alternateSwatches.options.hasOwnProperty(alternateOptionId) ?
                    this.options.jsonConfig.alternateSwatches.options[alternateOptionId] : null;
                if (!alternateOption) {
                    return;
                }
                this._replaceSwatchImage(optionId, alternateOption);
            }, this);
        },

        _returnOriginalSwatches: function () {
            var originalOptionIds = _.map(
                this.options.jsonConfig.alternateSwatches.mapping,
                function (mapping) {
                    return _.keys(mapping);
                }
            );
            originalOptionIds = [].concat.apply([], originalOptionIds);
            _.each(originalOptionIds, function (optionId) {
                var originalOption = null;
                _.each(this.options.jsonSwatchConfig, function (optionsObj, attributeId) {
                    var option = optionsObj.hasOwnProperty(optionId) ?
                        optionsObj[optionId] : null;
                    if (option) {
                        originalOption = option;
                    }
                });
                if (originalOption) {
                    this._replaceSwatchImage(optionId, originalOption);
                }
            }, this)
        },

        _replaceSwatchImage: function (optionId, option) {
            if (!this.optionElements) {
                this.optionElements = {};
                const
                    $optionDivs = $('[data-option-id]'),
                    self = this;
                $optionDivs.each(function () {
                    const element = this;
                    self.optionElements[element.dataset.optionId] = {
                        optionElement: element,
                        imageElement: element?.firstElementChild?.firstElementChild
                    };
                });
            }

            const
                optionElement = this.optionElements[optionId]?.optionElement,
                imageElement = this.optionElements[optionId]?.imageElement;

            if (optionElement) {
                optionElement.dataset.tooltipThumb = option.thumb;
                optionElement.dataset.tooltipValue = option.thumb;
            }

            if (imageElement) {
                imageElement.src = option.value;
                imageElement.dataset.original = option.value;
            }
        }
    };

    var tooltipWidget = {
        options: {
            delay: 200,                             //how much ms before tooltip to show
            tooltipClass: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltipClass),
                timer,
                type = parseInt($this.data('option-type'), 10),
                label = $this.data('option-label'),
                thumb = $this.data('option-tooltip-thumb'),
                value = $this.data('option-tooltip-value'),
                width = $this.data('thumb-width'),
                height = $this.data('thumb-height'),
                $image,
                $title,
                $corner;

            if (!$element.length) {
                $element = $('<div class="' +
                    $widget.options.tooltipClass +
                    '"><div class="image"></div><div class="title"></div><div class="corner"></div></div>'
                );
                $('body').append($element);
            }

            $image = $element.find('.image');
            $title = $element.find('.title');
            $corner = $element.find('.corner');

            $this.hover(function () {
                if (!$this.hasClass('disabled')) {
                    timer = setTimeout(
                        function () {
                            var leftOpt = null,
                                leftCorner = 0,
                                left,
                                $window;

                            if (type === 2) {
                                // Image
                                $image.css({
                                    'background': 'url("' + $this.data('option-tooltip-thumb') + '") no-repeat center', //Background case
                                    'background-size': 'initial',
                                    'width': width + 'px',
                                    'height': height + 'px'
                                });
                                $image.show();
                            } else if (type === 1) {
                                // Color
                                $image.css({
                                    background: $this.data('option-tooltip-value')
                                });
                                $image.show();
                            } else if (type === 0 || type === 3) {
                                // Default
                                $image.hide();
                            }

                            $title.text(label);

                            leftOpt = $this.offset().left;
                            left = leftOpt + $this.width() / 2 - $element.width() / 2;
                            $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            leftCorner = 0;

                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + $this.width() / 2 - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top: $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                clearTimeout(timer);
            });

            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    }

    return function (targetWidget) {
        $.widget('mage.SwatchRenderer', targetWidget, swatchRendererMixin);
        $.widget('mage.SwatchRendererTooltip', tooltipWidget);
        return $.mage.SwatchRenderer;
    };
});
