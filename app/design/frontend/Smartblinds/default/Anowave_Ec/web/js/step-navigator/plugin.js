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
 */define(function(){"use strict";return function(i){var t=i.steps;return i.next=function(){var e=0,n;t().sort(this.sortItems).forEach(function(o,s){o.isVisible()&&(o.isVisible(!1),e=s)}),t().length>e+1&&(n=t()[e+1].code,typeof AEC!="undefined"&&typeof AEC.Checkout.step!="undefined"&&AEC.Checkout.step(e,e+1,n),t()[e+1].isVisible(!0),this.setHash(n),document.body.scrollTop=document.documentElement.scrollTop=0)},i}});
