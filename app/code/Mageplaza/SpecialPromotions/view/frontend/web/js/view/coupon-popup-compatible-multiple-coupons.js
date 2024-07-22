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
    'Mageplaza_SpecialPromotions/js/view/coupon-popup',
    'Mageplaza_MultipleCoupons/js/model/checkout',
    'Mageplaza_MultipleCoupons/js/action/apply-coupon',
    'Mageplaza_MultipleCoupons/js/action/cancel-coupon',
    'mage/translate'
], function (_, ko, $, Component, modelCheckout, applyAction, cancelAction, $t) {
    'use strict';

    var couponDelimiter = modelCheckout.couponDelimiter;

    return Component.extend({
        defaults: {
            template: 'Mageplaza_SpecialPromotions/coupon-popup-compatible-multiple-coupons'
        },
        couponsForCancel: ko.observableArray([]),
        couponsForApply: ko.observableArray([]),
        arrayCode: modelCheckout.arrayCode,
        isLoading: modelCheckout.isLoading,
        isApplied: modelCheckout.isApplied,
        couponCode: modelCheckout.couponCode,
        inputCode: modelCheckout.inputCode,

        showModal: function () {
            this._super();
            this.couponsForApply([]);
            this.couponsForCancel([]);
        },

        applySingleCoupon: function () {
            var form      = $('#special-discount-mpmultiplecoupons-form'),
                arrayCode = this.couponCode() ? this.couponCode().split(couponDelimiter) : [];

            form.validation();
            if (form.valid() && this.inputCode()) {
                arrayCode.push(this.inputCode());
                applyAction(arrayCode.join(couponDelimiter), false);
            }
        },

        cancelSingleCoupon: function (code) {
            var arrayCode = this.couponCode() ? this.couponCode().split(couponDelimiter) : [];

            arrayCode = _.without(arrayCode, code);
            if (arrayCode.length) {
                applyAction(arrayCode.join(couponDelimiter), true);
            } else {
                cancelAction();
            }
        },

        addCouponCode: function (coupon) {
            var inputCouponElement = $('.coupon-rule input#' + coupon.coupon_id + '-' + coupon.coupon_code),
                coupons            = this.couponCode().split(couponDelimiter);

            if (inputCouponElement.is(':checked') && inputCouponElement.parents('.auto-generation-coupons').length) {
                _.each(inputCouponElement.parent().siblings(), function (element) {
                    $('.checkbox#' + element.firstElementChild.id).prop('checked', false);
                });
            }
            if (_.indexOf(coupons, coupon.coupon_code) !== -1) {
                if (inputCouponElement.is(':checked')) {
                    return true;
                }
                this.couponsForCancel(_.without(this.couponsForCancel(), coupon.coupon_code));
                this.couponsForCancel.push(coupon.coupon_code);
            } else {
                this.couponsForApply(_.without(this.couponsForApply(), coupon.coupon_code));

                if (inputCouponElement.is(':checked')) {
                    this.couponsForApply.push(coupon.coupon_code);
                }
            }

            return true;
        },

        completeCoupon: function () {
            var self            = this,
                isApplyOrCancel = false,
                allowCloseModal = true,
                arrayCode       = self.couponCode() ? self.couponCode().split(couponDelimiter) : [];

            this.isLoading(true);
            if (!this.couponCode() && !this.couponsForApply().length) {
                allowCloseModal = false;
                this.errorValidationMessage($t('Please enter or select a coupon code.'));
            }

            if (this.couponsForCancel().length) {
                _.each(this.couponsForCancel(), function (code) {
                    arrayCode = _.without(arrayCode, code);
                });
                isApplyOrCancel = true;
            }

            if (this.couponsForApply().length) {
                _.each(this.couponsForApply(), function (code) {
                    arrayCode.push(code);
                });
                isApplyOrCancel = true;
            }

            if (isApplyOrCancel) {
                if (arrayCode.length) {
                    applyAction(arrayCode.join(couponDelimiter), false);
                } else {
                    cancelAction();
                }
            }

            this.isLoading(false);
            if (allowCloseModal) {
                this.closeModal();
            }
        },

        cancelCoupon: function () {
            var isReloadPage = false;

            if (this.couponCode()) {
                cancelAction();
                if ($('body').hasClass('checkout-cart-index')) {
                    isReloadPage = true;
                }
            }

            if (isReloadPage) {
                setTimeout(function () {
                    location.reload();
                }, 3000);
            }

            this.closeModal();
        },

        isCouponChecked: function (coupon) {
            return _.indexOf(this.couponCode().split(couponDelimiter), coupon.coupon_code) !== -1;
        }
    });
});
