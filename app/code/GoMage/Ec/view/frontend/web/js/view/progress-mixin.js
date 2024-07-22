define([
    'jquery',
    'underscore'
], function (
    $,
    _
) {
    'use strict';

    var $window = $(window);
    var oncePool = {};

    function queue(callback) {
        if (typeof AEC !== 'undefined') {
            if ('undefined' !== typeof AEC.Const && 'undefined' !== typeof dataLayer) {
                if (AEC.Const.COOKIE_DIRECTIVE) {
                    AEC.CookieConsent.queue(callback).process();
                } else {
                    callback.apply(window,[]);
                }
            }
        }
    }

    function getSendSwatchEventCall(swatchOptionElement) {
        return function() {
                var $option = $(swatchOptionElement),
                    $attribute = $option.closest('.swatch-attribute'),
                    attrCode = $attribute.data('attribute-code'),
                    step = 0;
                if (oncePool[attrCode]) {
                    return;
                }
            if(oncePool['widthheight'] || attrCode == 'montage') {
                step = getStep(attrCode);
                var data = {
                    step: step,
                    stepname: attrCode,
                    option: $option.data('option-label')
                };
                oncePool[attrCode] = data;
                pushToDataLayer(data);
            }
        }
    }

    function getSendOptionsEventCall(optionElement) {
        return function() {
            var $option = $(optionElement), optionCode = $option.data('option-code');
            var option = '';
            var step = 0;
            if ($option.data('option-element-type') === 'text') {
                option = $option.text();
            }
            if (_.contains(['image', 'color'], $option.data('option-element-type'))) {
                option = $option.find('.mageworx-swatch-info').text();
            }
            if (oncePool[optionCode]) {
                return;
            }
            if(oncePool['widthheight'] || optionCode == 'montage') {
                step = getStep(optionCode);
                var data = {
                    step: step,
                    stepname: optionCode,
                    option: option
                };
                oncePool[optionCode] = data;
                pushToDataLayer(data);
            }
        }
    }

    function getSendWidthHeightEventCall(width, height) {
        return function() {
            if (!width || !height || oncePool['widthheight']) {
                return;
            }
            var data = {
                step: '2',
                stepname: 'width_height',
                option: width+' x '+height
            };
            oncePool['widthheight'] = data;
            pushToDataLayer(data);
        }
    }

    function pushToDataLayer(data) {
        checkPrevSteps(data);
        data.event = 'virtualVariantView';
        dataLayer.push(data);
    }

    function checkPrevSteps(data) {
        switch (data["stepname"]) {
            case "motor_side":
                swatchPrevStep("system_color");
            case "system_color":
                swatchPrevStep("system_size");
            case "system_size":
                swatchPrevStep("system_type");
            case "system_type":
                swatchPrevStep("color");
            case "color":
                swatchPrevStep("transparency");
                break;
        }
    }

    function swatchPrevStep(stepName) {
        if (!oncePool[stepName]) {
            getSendSwatchEventCall($("[data-attr-name=\""+stepName+"\"]").closest(".swatch-attribute").find(".selected").eq(0))();
        }
    }

    function getStep(option) {
        var step = 0;
        switch(option){
            case 'montage':
                step = 1;
                break;
            case 'transparency':
                step = 3;
                break;
            case 'color':
                step = 4;
                break;
            case 'system_type':
                step = 5;
                break;
            case 'system_size':
                step = 6;
                break;
            case 'system_color':
                step = 7;
                break;
            case 'motor_side':
                step = 8;
                break;
        }
        return step;
    }

    var mixin = {
        _create: function () {
            $window.on('swatches-click', function (e, swatchOptionElement) {
                queue(getSendSwatchEventCall(swatchOptionElement));
            });
            $window.on('options-click', function (e, optionElement) {
                queue(getSendOptionsEventCall(optionElement));
            });
            $window.on('option-width-height-change', function (e, width, height) {
                queue(getSendWidthHeightEventCall(width, height));
            });
            return this._super();
        }
    };

    return function (targetWidget) {
        $.widget('mage.progressButton', targetWidget, mixin);
        return $.mage.progressButton;
    };
});
