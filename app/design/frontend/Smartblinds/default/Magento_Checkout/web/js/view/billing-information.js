define(["jquery","uiComponent","Magento_Checkout/js/model/quote","Magento_Customer/js/customer-data","Magento_Checkout/js/model/step-navigator","Magento_Checkout/js/view/shipping-information"],function(l,r,t,i,o){"use strict";var n=i.get("directory-data"),s=i.get("customer"),u=i.get("checkout-data");return r.extend({defaults:{template:"Magento_Checkout/billing-address/billing-information"},currentBillingAddress:t.billingAddress,initialize:function(){return this._super(),this},isVisible:function(){return o.isProcessed("shipping")},isBillingNotSameAsShipping:function(){return t.billingAddress()!=null&&t.billingAddress().getCacheKey()!=t.shippingAddress().getCacheKey()},getCountryName:function(e){return n()[e]!=null?n()[e].name:""},getCustomerEmail:function(){let e="";return s().email?e=s().email:e=u().inputFieldEmailValue,e}})});
