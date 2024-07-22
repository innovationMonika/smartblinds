/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * http://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2021 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */define(["jquery","mage/utils/wrapper"],function(d,C){"use strict";return function(u){return C.wrap(u,function(t,e,n){if(typeof AEC=="undefined"||typeof AEC.Const=="undefined")return t(e,n);if(typeof AEC.Const.CHECKOUT_STEP_PAYMENT!="undefined"){var E=e.method;AEC.Checkout.stepOption(AEC.Const.CHECKOUT_STEP_PAYMENT,E)}return typeof data!="undefined"&&typeof AEC.Const.CHECKOUT_STEP_ORDER!="undefined"&&(data.ecommerce.checkout.actionField.step=AEC.Const.CHECKOUT_STEP_ORDER,AEC.Cookie.checkout(data).push(dataLayer),dataLayer.push({event:"placeOrder"})),t(e,n)})}});
