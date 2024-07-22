define(["jquery","ko","Magento_Ui/js/form/form","Magento_Checkout/js/model/quote","MageWorx_MultiFees/js/model/product-fee","MageWorx_MultiFees/js/action/apply-product-fees","MageWorx_MultiFees/js/model/fee-messages","mage/translate","Magento_Checkout/js/action/get-payment-information","Magento_Checkout/js/model/totals","Magento_Checkout/js/model/step-navigator","Magento_Checkout/js/model/cart/totals-processor/default","underscore"],function(s,a,r,n,u,d,l,c,m,o,i,g,f){"use strict";var p=c("You need to select required additional fees to proceed to checkout"),t=a.observable(!1);return r.extend({initialize:function(){return this._super(),this},isLoading:t,onSubmit:function(){if(this.source.set("params.invalid",!1),this.source.trigger("mageworxProductFeeForm.data.validate"),this.source.get("params.invalid"))return l.addErrorMessage({message:p}),this;t(!0);var e=this.source.get("mageworxProductFeeForm");return e.type=this.typeId,e.quote_item_id=this.itemId,d(e,t,"mageworx-fee-form"+this.itemId),this},isDisplayTitle:function(){return u.allData().is_display_title},isDisplayed:function(){return!0},updateTotals:function(){if(t(!1),f.isEmpty(i.steps())&&g.estimateTotals(n.shippingAddress()),i.getActiveItemIndex()){var e=s.Deferred();o.isLoading(!0),m(e),s.when(e).done(function(){o.isLoading(!1)})}}})});
