/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * https://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2023 Anowave (https://www.anowave.com/)
 * @license  	https://www.anowave.com/license-agreement/
 */define(["jquery"],function(e){"use strict";return function(r){return e.widget("mage.configurable",r,{_setupChangeEvents:function(){var i=this;if(this._super(),typeof AEC.Const=="undefined")return this;e.each(this.options.settings,e.proxy(function(a,u){e(u).on("change.ec",e.proxy(function(){(function(n){AEC.Const.COOKIE_DIRECTIVE?AEC.CookieConsent.queue(n).process():n.apply(window,[])})(function(n){if(n&&typeof n.simpleProduct!="undefined"){var t={},o=n.simpleProduct.toString();return typeof AEC.CONFIGURABLE_SIMPLES=="undefined"?function(){console.log("Skipping virtualVariantView event.")}:(AEC.CONFIGURABLE_SIMPLES.hasOwnProperty(o)&&(t=AEC.CONFIGURABLE_SIMPLES[o]),function(){dataLayer.push({event:"virtualVariantView",ecommerce:{currencyCode:AEC.currencyCode,detail:{actionField:{list:"Configurable variants"},products:[t]}}}),e('[data-event="addToCart"]').data("simple-id",t.id).attr("data-simple-id",t.id),typeof fbq!="undefined"&&fbq("track","CustomizeProduct",{eventID:AEC.UUID.generate({event:"CustomizeProduct"})})})}else return function(){dataLayer.push({event:"resetConfigurableSelection"})}}(this))},this))},this))},_changeProductImage:function(){this._super(),typeof dataLayer!="undefined"&&(dataLayer.push({event:"changeProductImage"}),typeof fbq!="undefined"&&function(i){AEC.Const.COOKIE_DIRECTIVE?AEC.CookieConsent.queue(i).process():i.apply(window,[])}(function(){return function(){fbq("track","ChangeProductImage")}}()))}}),e.mage.configurable}});
