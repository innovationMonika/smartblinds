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
 */define(["jquery","mage/utils/wrapper","Magento_Checkout/js/model/quote"],function(p,c,s){"use strict";return function(r){return c.wrap(r,function(a,n){return typeof dataLayer!="undefined"&&typeof data!="undefined"&&function(m,y,C){if(typeof fbq!="undefined"){for(var o=[],d=data.ecommerce.checkout.products.length,t=0,f=data.ecommerce.checkout.products.length;t<f;t++)o.push(data.ecommerce.checkout.products[t].id);(function(e){AEC.Const.COOKIE_DIRECTIVE?AEC.CookieConsent.queue(e).process():e.apply(window,[])})(function(e,u,i){return function(){fbq("track","AddPaymentInfo",{value:e.total,content_name:"checkout",content_ids:u,num_items:i,currency:AEC.currencyCode,content_type:u.length>1?"product group":"product"})}}(info,o,d))}}(dataLayer,jQuery,n),a(n)})}});
