/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SpecialPromotions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'underscore',
    'ko',
    'jquery',
    'Magento_SalesRule/js/view/payment/discount',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'uiRegistry',
    'rjsResolver',
    'Mageplaza_SpecialPromotions/js/action/set-coupon-code',
    'Mageplaza_SpecialPromotions/js/action/cancel-coupon'
], function (_, ko, $, Component, $t, modal, quote, uiRegistry, resolver, setCouponCodeAction, cancelCouponAction) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_SpecialPromotions/coupon-popup'
        },
        couponForApply: ko.observable(),
        isLoading: ko.observable(false),
        allowCloseModal: ko.observable(false),
        errorValidationMessage: ko.observable(),
        specialPromotionsRules: ko.computed(function () {
            var extensionAttributes = quote.getTotals()().extension_attributes;

            if (extensionAttributes && extensionAttributes.hasOwnProperty('mp_coupon_popup_details')) {
                return extensionAttributes.mp_coupon_popup_details;
            }

            return [];
        }),

        initialize: function () {
            this._super();

            resolver(this.afterResolveDocument.bind(this));
        },

        afterResolveDocument: function () {
            var paymentComponent         = uiRegistry.get('checkout.steps.billing-step.payment'),
                specialPromotionsElement = $('#mp-special-promotions-popup'),
                body                     = $('body');

            if (paymentComponent && !body.hasClass('onestepcheckout-index-index')) {
                if (!paymentComponent.isVisible()) {
                    specialPromotionsElement.hide();
                }
                paymentComponent.isVisible.subscribe(function (value) {
                    if (value === true) {
                        specialPromotionsElement.show();
                    } else {
                        specialPromotionsElement.hide();
                    }
                });
            }

            if (body.hasClass('checkout-cart-index')) {
                specialPromotionsElement.detach().appendTo($('#cart-totals'));
            }
        },

        setModalElement: function (element) {
            var options = {
                'type': 'popup',
                'title': $t('ADD COUPON CODE'),
                'modalClass': 'promotions-coupon-popup',
                'responsive': true,
                'innerScroll': true,
                'buttons': []
            };

            if (this.couponCode()) {
                options.title = $t('EDIT COUPON CODE');
            }

            this.modalWindow = element;
            modal(options, $(this.modalWindow));
        },

        showModal: function () {
            this.couponForApply(null);
            this.errorValidationMessage(null);
            $(this.modalWindow).modal('openModal');
        },

        closeModal: function () {
            $(this.modalWindow).modal('closeModal');
        },

        getPopupButtonLabel: function () {
            if (this.couponCode()) {
                return $t('Edit Coupon');
            }

            return $t('Add Coupon');
        },

        getRuleLabel: function (rule) {
            if (rule.rule_label) {
                return rule.rule_label;
            }

            return rule.rule_name;
        },

        getNotAutoGenerationCoupon: function (rule) {
            if (!rule.use_auto_generation) {
                var coupon = rule.coupons[0].coupon_code.toUpperCase();

                if (coupon.length > 6) {
                    coupon = coupon.slice(0, 5) + '...';
                }

                return coupon;
            }
        },

        getExpiredDate: function (rule) {
            if (rule.expired_date) {
                return $t('Expiry date: ') + rule.expired_date;
            }

            return $t('Permanent');
        },

        cancelCoupon: function () {
            cancelCouponAction.resetCallBack();
            if (this.couponCode()) {
                this.isLoading(true);
                cancelCouponAction.registerSuccessCallback(this.callbackDiscountBlockReload);
                cancelCouponAction.registerSuccessCallback(this.callbackSuccess.bind(this));
                cancelCouponAction.registerFailCallback(this.callbackDiscountBlockReload);
                cancelCouponAction.registerFailCallback(this.callbackFail.bind(this));
                this.cancel();
            }
        },

        completeCoupon: function () {
            var coupon = this.couponForApply();

            setCouponCodeAction.resetCallBack();
            cancelCouponAction.resetCallBack();
            if (!this.couponCode() && !this.couponForApply()) {
                this.errorValidationMessage($t('Please enter or select a coupon code.'));
            }

            if (this.couponCode() && coupon && this.couponCode() !== this.couponForApply()) {
                cancelCouponAction.registerSuccessCallback(this.callbackCancel.bind(this));
                cancelCouponAction.registerFailCallback(this.callbackFail.bind(this));
                this.isLoading(true);
                this.cancel();
            } else {
                if (!this.couponCode() && this.couponForApply()) {
                    this.couponCode(this.couponForApply());
                }

                if (this.couponCode()) {
                    this.registerApplyCallBack();
                    this.isLoading(true);
                    this.apply();
                }
            }
        },

        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(this.couponCode(), this.isApplied);
            }
        },

        cancel: function () {
            if (this.validate()) {
                this.couponCode('');
                cancelCouponAction(this.isApplied);
            }
        },

        callbackDiscountBlockReload: function () {
            var elementToReload = window.location.href + ' #discount-coupon-form';

            if ($('body').hasClass('checkout-cart-index')) {
                $('#discount-coupon-form').parent().load(elementToReload, function (response, status) {
                    if (status === 'success') {
                        $('#discount-coupon-form').trigger('contentUpdated');
                    }
                });
            }
        },

        callbackSuccess: function () {
            this.isLoading(false);
            this.closeModal();
        },

        callbackFail: function () {
            this.isLoading(false);
        },

        callbackCancel: function () {
            this.callbackDiscountBlockReload();
            if (this.couponForApply()) {
                this.couponCode(this.couponForApply());
                this.registerApplyCallBack();
                this.apply();
            }
        },

        registerApplyCallBack: function () {
            setCouponCodeAction.registerSuccessCallback(this.callbackDiscountBlockReload);
            setCouponCodeAction.registerSuccessCallback(this.callbackSuccess.bind(this));
            setCouponCodeAction.registerFailCallback(this.callbackFail.bind(this));
        },

        addCouponCode: function (coupon) {
            var inputCouponElement = $('.coupon-rule input#' + coupon.coupon_id + '-' + coupon.coupon_code);

            this.couponForApply(null);
            _.each($('[name="coupon-checkbox-input"]'), function (element) {
                if (element.id !== inputCouponElement.attr('id')) {
                    $('.coupon-rule input#' + element.id).prop('checked', false);
                }
            });
            if (inputCouponElement.is(':checked')) {
                this.couponForApply(coupon.coupon_code);
            }

            return true;
        },

        isCouponChecked: function (coupon) {
            return coupon.coupon_code === this.couponCode();
        },

        copyCoupon: function (couponCode, element, e) {
            var couponTextArea = document.createElement('textarea');

            couponTextArea.value = couponCode;
            document.body.appendChild(couponTextArea);
            couponTextArea.select();
            document.execCommand('copy');
            document.body.removeChild(couponTextArea);

            e.currentTarget.querySelector('span').setAttribute('class', 'mp-tooltipped');
            e.currentTarget.querySelector('span').setAttribute('aria-label', $t('Copied!'));
        }
    });
});
