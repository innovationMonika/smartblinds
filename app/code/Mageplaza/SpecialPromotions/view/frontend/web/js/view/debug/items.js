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
 */define(["ko","jquery","uiComponent","Magento_Checkout/js/model/quote","Magento_Catalog/js/price-utils","mage/translate"],function(l,f,m,o,c,u){"use strict";var d=l.observable({});function y(a,i){var n,t;if(!a)return null;for(n in a.total_segments)if(t=a.total_segments[n],t.code==i)return t;return null}function p(a){var i={},n=y(a,"discount");if(n&&n.extension_attributes&&n.extension_attributes.discount_details){var t=n.extension_attributes.discount_details;for(var r in t)if(t.hasOwnProperty(r)&&t[r].hasOwnProperty("items")){for(var s in t[r].items)if(t[r].items.hasOwnProperty(s)){var e=t[r].items[s];i[e.item_id]||(i[e.item_id]={amount:0,qty:0,rules:[]}),i[e.item_id].amount+=e.amount,i[e.item_id].qty=e.amount>0?i[e.item_id].qty+e.qty:i[e.item_id].qty,i[e.item_id].rules.push({rule_id:t[r].rule_id,title:t[r].title,amount:e.amount,qty:e.qty})}}}d(i)}return p(o.totals()),o.totals.subscribe(function(a){p(a)}),m.extend({defaults:{itemId:0},initialize:function(){this._super(),this.message=l.computed(function(){var a=d(),i="";if(a.hasOwnProperty(this.itemId)){var n=a[this.itemId];if(n.hasOwnProperty("amount")&&n.amount>0){for(var t in n)if(n.hasOwnProperty(t))switch(t){case"amount":i+='<p><span style="display:inline-block; width: 40%; min-width: 110px; padding-right: 10px;">'+u("Discount amount:")+"</span>",i+=this.getFormattedPrice(n[t])+"</p>";break;case"qty":i+='<p><span style="display:inline-block; width: 40%; min-width: 110px; padding-right: 10px;">'+u("Qty discount:")+"</span>",i+=n[t]+"</p>";break;case"rules":var r="";for(var s in n[t])if(n[t].hasOwnProperty(s)){var e=n[t][s];e.amount>0&&(r+='<p><span style="display:inline-block; width: 60%; padding-right: 10px; padding-left: 20px">'+e.title+" (ID: "+e.rule_id+", Qty: "+e.qty+"):</span>",r+=this.getFormattedPrice(e.amount)+"</p>")}r!==""&&(i+='<p><span style="display:inline-block;">'+u("Rule(s) applied:")+"</span></p>",i+=r);break}}}return i},this)},getFormattedPrice:function(a){return c.formatPrice(a,o.getPriceFormat())}})});
