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
    'jquery',
    'Mageplaza_SpecialPromotions/js/view/summary/discount',
    'Magento_Checkout/js/model/totals'
], function (_, $, Component, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_SpecialPromotions/summary/promotions_multiple_coupons_discount'
        },

        isDisplayed: function () {
            return parseFloat(this.getPureValue()) != 0; //eslint-disable-line eqeqeq
        },

        getTitle: function () {
            var segment = totals.getSegment('mpmultiplecoupons');

            return segment ? segment.title : '';
        },

        getCoupons: function () {
            var segment = totals.getSegment('mpmultiplecoupons');

            if (!segment) {
                return [];
            }

            if (typeof segment.value[0] === 'string') {
                segment.value = $.map(segment.value, function (value) {
                    return JSON.parse(value);
                });
            }

            return segment.value;
        },

        ruleTitle: function (data) {
            var coupon = _.findWhere(this.getCoupons(), {'ruleId': parseInt(data.rule_id)});

            if (coupon && coupon.hasOwnProperty('code')) {
                return data.title + ' ( ' + coupon['code'] + ' )';
            }

            return data.title;
        }
    });
});
