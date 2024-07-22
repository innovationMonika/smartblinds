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
 */define(["jquery","Magento_Catalog/js/price-utils","underscore","mage/template"],function(l,f,o,p){"use strict";return function(u){return l.widget("mage.priceBox",u,{map:{},reloadPrice:function(){o.each(this.cache.displayPrices,function(e,a){e.final=o.reduce(e.adjustments,function(t,i){return t+i},e.amount),a==="finalPrice"&&l("[id=product-addtocart-button]").attr("data-price",e.final).data("price",e.final)},this);let r={};if([...document.querySelectorAll("input[data-selector]:checked")].filter(e=>e.dataset.selector.indexOf("options")===0).forEach(e=>{let a=document.querySelector('label[for="'+e.id+'"]');if(a){let t=a.querySelector("span:first-child").innerText.trim();if(t){let i=e.closest(".control");if(i){let s=i.parentNode.querySelector("label:first-child").querySelector("span:first-child").innerText.trim(),n=s.split("").map(c=>c.charCodeAt(0)).reduce((c,d)=>c+d,0);r.hasOwnProperty(n)||(r[n]=[]),r[n].push({label:s,value:t})}}}}),Object.keys(r).length){let e={event:"customize",eventData:[]};Object.entries(r).forEach(([a,t])=>{e.eventData.push(t)}),dataLayer.push(e)}return this._super()}}),l.mage.priceBox}});
