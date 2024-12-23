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
 */define(["jquery","mage/utils/wrapper","Magento_Checkout/js/model/quote","Magento_SalesRule/js/model/payment/discount-messages","Magento_Checkout/js/action/set-payment-information-extended","Magento_Checkout/js/action/get-totals","Magento_SalesRule/js/model/coupon","Magento_Customer/js/model/customer"],function(e,u,t,a,l,d,n,c){"use strict";return function(i){return u.wrap(i,function(r,o){r(o),!(!c.isLoggedIn()&&t.guestEmail===null||o===null)&&e.when(l(a,{method:o.method},!0)).done(function(){var s=e.Deferred(),m=function(){t.totals()&&!t.totals().coupon_code&&(n.setCouponCode(""),n.setIsApplied(!1))};d([],s),e.when(s).done(m)})})}});
