define(["ko","Magento_Checkout/js/view/summary/abstract-total","Magento_Checkout/js/model/quote","Magento_Catalog/js/price-utils","Magento_Checkout/js/model/totals"],function(o,r,a,i,t){"use strict";return r.extend({defaults:{template:"MageWorx_MultiFees/summary/fee-tax"},totals:a.getTotals(),isDisplayed:function(){if(this.isFullMode()){var e=0;if(this.totals()&&t.getSegment("mageworx_shipping_fee_tax")&&(e=t.getSegment("mageworx_fee_tax").value,e>=0))return!0}return!1},getValue:function(){var e=0;return this.totals()&&t.getSegment("mageworx_shipping_fee_tax")&&(e=t.getSegment("mageworx_shipping_fee_tax").value),this.getFormattedPrice(e)},getBaseValue:function(){var e=0;return this.totals()&&(e=this.totals().base_fee),i.formatPrice(e,a.getBasePriceFormat())},formatPrice:function(e){return this.getFormattedPrice(e)},getDetails:function(){var e=t.getSegment("mageworx_shipping_fee_tax");if(e&&e.extension_attributes){var s=e.extension_attributes.mageworx_fee_details;return s}return[]}})});