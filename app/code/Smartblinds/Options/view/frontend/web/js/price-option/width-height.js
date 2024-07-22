define([
    'jquery',
    'underscore',
    'getSwatchSelectedProductId',
    'priceUtils',
    'mage/translate',
    'Smartblinds_Options/js/price-option/width-height/calculations',
    'Smartblinds_Options/js/model/price-calculator',
    'Smartblinds_Options/js/model/system',
    'Smartblinds_Options/js/model/option-params',
    'mage/validation',
    'priceOptions',
    'jquery-ui-modules/widget'
], function (
    $,
    _,
    getSwatchSelectedProductId,
    utils,
    $t,
    calculations,
    PriceCalculator,
    System,
    OptionParams
) {
    'use strict';

    /* CHANGES V4 @GOMAGE */
    /*
        - remove ratio formula and added additional error message (#1) to validate this
    */

    var $window = $(window);
    var globDisabledOptions = {};
    var missedProgressFirstClick = false;
    var isDisableEvents = false;

    $.widget('mage.priceOptionWidthHeight', {
        options: {
            formSelector: '#product_addtocart_form',
            valueFieldSelector: 'input[type=hidden]',
            widthFieldSelector: 'input[data-role=width]',
            heightFieldSelector: 'input[data-role=height]',
            systemTypeSelector: 'input[data-attr-name=system_type]',
            controlTypeSelector: 'input[data-attr-name=control_type]',
            systemSizeSelector: 'input[data-attr-name=system_size]',
            fabricSizeSelector: 'input[data-attr-name=fabric_size]',
            progressButtonSelector: '[data-role=progress-button]'
        },

        _create: function () {
            this
                ._initFields()
                ._initEvents()
                ._initOptionHandler()
                ._addValidatorMethod();
        },

        _initFields: function () {
            this.form = this.element.closest(this.options.formSelector);
            this.$labelValue = $(this.element).find('#value');
            this.$form = $(this.form);
            this.$valueField = $(this.element.find(this.options.valueFieldSelector));
            this.$widthField = $(this.element.find(this.options.widthFieldSelector));
            this.$heightField = $(this.element.find(this.options.heightFieldSelector));
            this.$progressButton = $(this.$form.find(this.options.progressButtonSelector));
            System.init(this.$valueField);
            OptionParams.init(this.$valueField);
            return this;
        },

        _initEvents: function () {
            this.$widthField.on('input', this._updateValueField.bind(this));
            this.$widthField.on('focusout', this._validateField.bind(this));
            this.$heightField.on('input', this._updateValueField.bind(this));
            this.$heightField.on('focusout', this._validateField.bind(this));

            this.$widthField.on('focusout', this._sendUpdateEvent.bind(this));
            this.$heightField.on('focusout', this._sendUpdateEvent.bind(this));

            $window.on('swatches-click', this._updateValueField.bind(this));
            $window.on('swatches-click', this._addValidatorWidthHeight.bind(this));
            $window.on('swatches-click', this._handleSystemSizeAvailability.bind(this));
            this.$progressButton.on('mousedown', this._updateValueField.bind(this));
            this.$progressButton.on('mouseup', this._validateField.bind(this));

            $window.on('priceOptionWidthHeightUpdate', this._updateValueField.bind(this));
            $window.on('priceOptionWidthHeightValidate', this._validateField.bind(this));
            $window.on('priceOptionWidthHeightSendUpdate', this._sendUpdateEvent.bind(this));

            if ($('body').hasClass('checkout-cart-configure')) {
                this.$valueField.addClass('selected');
                $window.trigger('update-steps');
            }

            return this;
        },

        _initOptionHandler: function () {
            var handlerId = this.$valueField.data('role'),
                extendData = {
                    optionHandlers: {}
                };
            extendData.optionHandlers[handlerId] = this._optionHandler.bind(this);
            this.form.priceOptions(extendData);
            return this;
        },

        _prepareInputValue: function ($field) {
            let value = $field.val().replace(',', '.');
            value = value ? value : 0;
            return parseInt(parseFloat(value) * 10);
        },

        _isChainProduct: function () {
            let hash = new URLSearchParams(window.location.hash.substring(1)),
                sku = hash.get('sku') ? hash.get('sku').toLowerCase() : null;
            return (sku && sku.includes('chain'));
        },

        _updateValueField: function (event, elem, isSelectionEmulated) {
            if (isSelectionEmulated) {
                return;
            }
            const value = {
                width: this._prepareInputValue(this.$widthField),
                height: this._prepareInputValue(this.$heightField)
            };
            this.$valueField.val(JSON.stringify(value));
            value['system_type'] = $(this.$form.find(this.options.systemTypeSelector)).val();
            if ($(this.$form.find(this.options.controlTypeSelector)).length > 0) {
                value['control_type'] = $(this.$form.find(this.options.controlTypeSelector)).val();
            } else {
                value['control_type'] = (this._isChainProduct()) ? "chain" : "motor";
            }
            value['system_size'] = $(this.$form.find(this.options.systemSizeSelector)).val();
            _.each(value, function (value, key) {
                this.$valueField.data(key, value);
            }, this);
            this._updatePlaceholders(value);
            const
                widthCm = value.width / 10,
                heightCm = value.height / 10;
            if (Number.isInteger(value.width) && Number.isInteger(value.height)) {
                this.$labelValue.text(widthCm + ' x ' + heightCm + ' cm');
            }

            var system = System.get();
            var systemValues = window.jsonConfig.systemTypeValues;
            if(systemValues[system.systemType] == "tdbu") {
                $('.product-option-info-icon[data-option-modal="product_option_modal_motor_side"]').eq(0).closest(".product-option").addClass("motor_side-visible-0");
            } else {
                $('.product-option-info-icon[data-option-modal="product_option_modal_motor_side"]').eq(0).closest(".product-option").removeClass("motor_side-visible-0");
            }

            this.$valueField.trigger('change');
        },

        _sendUpdateEvent: function () {
            var width = this.$valueField.data('width'),
                height = this.$valueField.data('height');
            if (Number.isInteger(width) && Number.isInteger(height) && this.$widthField.valid()) {
                var widthCm = width / 10,
                    heightCm = height / 10;
                $window.trigger('option-width-height-change', [widthCm, heightCm]);
            }
        },

        _validateField: function (event) {
            if (this.$widthField.is(':focus') || this.$heightField.is(':focus')) {
                this.$valueField.removeClass('selected');
                return;
            }
            this.$widthField.val(this.$widthField.val().replace(",", "."));
            this.$heightField.val(this.$heightField.val().replace(",", "."));
            this._handleSystemSizeAvailability();
            if (this.$widthField.val() && this.$heightField.val() && this.$widthField.valid()) {
                this.$valueField.addClass('selected');
                $window.trigger('update-steps');
            } else {
                this.$valueField.removeClass('selected');
            }
        },

        _handleSystemSizeAvailability: function () {
            const $systemSizeParent = $(this.$form.find(this.options.systemSizeSelector)).parent();
            $systemSizeParent
                .find('[data-option-id]')
                .attr('disabled', false).prop('disabled', false);

            const systemSizeValues = window.jsonConfig.systemSizeValues;
            const systemSizeValuesIdByName = _.invert(systemSizeValues);
            const smallSystemSizeIndex = systemSizeValuesIdByName['small'];
            const mediumSystemSizeIndex = systemSizeValuesIdByName['medium'];
            let system = System.get();
            if (!system) {
                return;
            }
            let smallSystem = null;
            let smallSystemProductId = parseInt(getSwatchSelectedProductId());
            if (systemSizeValues[system.systemSize] === 'medium') {
                if (isDisableEvents === true) {
                    return;
                }
                isDisableEvents = true;
                $systemSizeParent
                    .find('[data-option-id="' + smallSystemSizeIndex + '"]')
                    .trigger('click');
                smallSystemProductId = parseInt(getSwatchSelectedProductId());
                smallSystem = System.get();
                $systemSizeParent
                    .find('[data-option-id="' + mediumSystemSizeIndex + '"]')
                    .trigger('click');
                isDisableEvents = false;
            }

            const smallSystemProductOptionParams = OptionParams.get(smallSystemProductId, smallSystem);
            if (!smallSystemProductOptionParams) {
                return;
            }
            system = smallSystemProductOptionParams.system;
            const
                width = parseFloat(this.$valueField.data('width')),
                height = parseFloat(this.$valueField.data('height')),
                maxWidth = calculations.calcMaxWidth(smallSystemProductOptionParams),
                maxHeight = calculations.calcMaxHeight(smallSystemProductOptionParams);
            if ((width > maxWidth || height > maxHeight)
                || (parseFloat(smallSystemProductOptionParams.product.thickness) >= 0.5 && width >= 2000)
            ) {
                if (systemSizeValues[system.systemSize] === 'small') {
                    $systemSizeParent
                        .find('[data-option-id="' + mediumSystemSizeIndex + '"]')
                        .trigger('click');
                }
                $systemSizeParent
                    .find('[data-option-id="' + smallSystemSizeIndex + '"]')
                    .attr('disabled', true).prop('disabled', true);
            }
        },

     _updatePlaceholders: function (value) {

        var system = System.get();
       if (!system) {
                return;
            }

        const systemPlaceholderID = window.jsonConfig.systemsPlaceholder[system.id];
           /* this.$widthField.attr('placeholder', this.$widthField.data('placeholder'));
            this.$heightField.attr('placeholder', this.$heightField.data('placeholder'));

            const optionParams = OptionParams.get();

            if (!optionParams) {
                return;
            }

            const system = optionParams.system;
            const systemValues = window.jsonConfig.systemTypeValues;
            const toString = $t(' to '); */
         this.$widthField.attr('placeholder', systemPlaceholderID.widthPlaceHolder);
         this.$heightField.attr('placeholder', systemPlaceholderID.heightPlaceHolder);
            /*if (!value || (!value.width && !value.height)) {
                let systemMinWidth = System.getCommonMinWidth();
                if (systemValues[system?.systemType] === "tdbu") {
                    systemMinWidth = system.minWidth;
                }
                this.$widthField.attr('placeholder', (systemMinWidth / 10) + toString + (System.getCommonMaxWidth() / 10) + ' cm');
                this.$heightField.attr('placeholder', (System.getCommonMinHeight() / 10) + toString + (System.getCommonMaxHeight() / 10) + ' cm');
            }
            if (value.width && !value.height) {
                const maxHeight = calculations.calcMaxHeight(optionParams);
                this.$heightField.attr('placeholder', (system.minHeight / 10) + toString + (maxHeight / 10) + ' cm');
            }
            if (!value.width && value.height) {
                const maxWidth = calculations.calcMaxWidth(optionParams);
                this.$widthField.attr('placeholder', (system.minWidth / 10) + toString + (maxWidth / 10) + ' cm');
            }*/
        },

        _optionHandler: function (element, optionConfig) {
            var optionId = utils.findOptionId(element),
                overhead = optionConfig[optionId].prices,
                changesOptionId = 'options[' + optionId + ']',
                changes = {};

            const price = PriceCalculator.calculateWidthHeightFinalPrice();

            if (price === null) {
                changes[changesOptionId] = {};
                return changes;
            }

            overhead.basePrice.amount = price;
            overhead.finalPrice.amount = price;
            overhead.oldPrice.amount = price;
            overhead.oldPrice.amount_excl_tax = price;
            overhead.oldPrice.amount_incl_tax = price;
            changes[changesOptionId] = overhead;
            return changes;
        },

        _addValidatorWidthHeight: function () {
            let width = this.$valueField.data('width'),
                height = this.$valueField.data('height');
            if (width && height) {
                let systemMotor = System.getByType("motor"),
                    systemChain = System.getByType("chain");

                if (systemMotor && width < systemMotor.minWidth && systemMotor.isChainCustomerGroup == 1) {
                    $("#option-label-control_type-" + systemMotor.controlTypeData["attributeId"] + "-item-" + systemMotor.controlTypeData["options"]["chain"]).removeClass("disabled");
                    $("#option-label-control_type-" + systemMotor.controlTypeData["attributeId"] + "-item-" + systemMotor.controlTypeData["options"]["motor"]).addClass("disabled");
                } else if (systemChain && width > systemChain.maxWidth) {
                    $("#option-label-control_type-" + systemMotor.controlTypeData["attributeId"] + "-item-" + systemMotor.controlTypeData["options"]["motor"]).removeClass("disabled");
                    $("#option-label-control_type-" + systemMotor.controlTypeData["attributeId"] + "-item-" + systemMotor.controlTypeData["options"]["chain"]).addClass("disabled");
                }
            }
        },

        _addValidatorMethod: function () {
            var self = this;
            $.validator.messages = {
                required: $t("Don\'t forget to fill in the width and height of your window recess here")
            };
            $.validator.addMethod('width-height', function (value, element) {
                var optionParams = OptionParams.get(),
                    $element = $(element),
                    systemMotor = System.getByType("motor"),
                    systemChain = System.getByType("chain");

                if (!self.$valueField.data('width') || !self.$valueField.data('height')) {
                    if (self.$progressButton.hasClass('progress-btn')) {
                        $element.data('error-message', $t("Don\'t forget to fill in the width and height of your window recess here"));
                        return false;
                    } else {
                        return true;
                    }
                }

                if (!optionParams) {
                    $element.data('error-message', $t('This product is unavailable'));
                    return false;
                }

                var system = optionParams.system,
                    width = self.$valueField.data('width'),
                    height = self.$valueField.data('height'),
                    maxWidth = calculations.calcMaxWidth(optionParams),
                    maxHeight = calculations.calcMaxHeight(optionParams);

                if (!height || !width) {
                    return false;
                } else if (height / width > 3) {
                    $element.data(
                        'error-message',
                        $t('Your product has a width/height ratio greater than 1:3. This is greater than our recommended ratio. Choose dimensions that fall within this ratio.')
                    );
                    return false;
                } else if (
                    ($(self.$form.find(self.options.controlTypeSelector)).length > 0)
                    && width < systemMotor.minWidth
                    && system.controlTypeData["options"][system.controlType] != "chain"
                    && system.isChainCustomerGroup == 1
                ) {
                    $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["chain"]).removeClass("disabled");
                    $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["chain"]).trigger("click");
                    $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["motor"]).addClass("disabled");
                    console.log('De gekozen breedte van ' + (width / 10) + ' cm is alleen mogelijk met een ketting bediend systeem.');
                } else if (
                    ($(self.$form.find(self.options.controlTypeSelector)).length > 0)
                    && system.controlTypeData["options"][system.controlType] == "chain"
                    && width > systemChain.maxWidth
                ) {
                    $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["motor"]).removeClass("disabled");
                    $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["motor"]).trigger("click");
                    $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["chain"]).addClass("disabled");
                    console.log('De gekozen breedte van ' + (width / 10) + ' cm is niet mogelijk met een ketting bediend systeem.');
                } else {
                    if (width > maxWidth && height > maxHeight) {
                        $element.data(
                            'error-message',
                            $t('Your product is too broad and therefore cannot be delivered. The maximum width for a product of %1 cm high is: %2 cm. Try a different fabric or choose different dimensions.')
                                .replace('%1', height / 10)
                                .replace('%2', maxWidth / 10)
                        );
                        return false;
                    }

                    if (width <= maxWidth && height > maxHeight) {
                        $element.data(
                            'error-message',
                            $t('Your product is too high and therefore cannot be delivered. The maximum height for a product of %1 cm wide is: %2 cm. Try a different fabric or choose different dimensions.')
                                .replace('%1', width / 10)
                                .replace('%2', maxHeight / 10)
                        );
                        return false;
                    }

                    if (width > maxWidth && height <= maxHeight) {
                        $element.data(
                            'error-message',
                            $t('Your product is too broad and therefore cannot be delivered. The maximum width for a product of %1 cm high is: %2 cm. Try a different fabric or choose different dimensions.')
                                .replace('%1', height / 10)
                                .replace('%2', maxWidth / 10)
                        );
                        return false;
                    }

                    if (system.minHeight > height || system.minWidth > width) {
                        $element.data(
                            'error-message',
                            $t('Your product is too small and therefore cannot be delivered. The minimum width is %1 cm and the minimum height is %2 cm.')
                                .replace('%1', system.minWidth / 10)
                                .replace('%2', system.minHeight / 10)
                        );
                        return false;
                    }

                    if (systemMotor && width < systemMotor.minWidth && system.isChainCustomerGroup == 1) {
                        $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["chain"]).removeClass("disabled");
                        $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["motor"]).addClass("disabled");
                    } else if (systemChain && width > systemChain.maxWidth) {
                        $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["motor"]).removeClass("disabled");
                        $("#option-label-control_type-" + system.controlTypeData["attributeId"] + "-item-" + system.controlTypeData["options"]["chain"]).addClass("disabled");
                    }
                }

                return true;

            }, function (params, element) {
                return $(element).data('error-message');
            });

            return this;
        }

    });

    return $.mage.priceOptionWidthHeight;
});
