define(["jquery","mage/utils/wrapper","uiRegistry","MageWorx_MultiFees/js/action/apply-fees","MageWorx_MultiFees/js/model/fee-messages"],function(u,s,t,n,g){"use strict";return function(o){return s.wrap(o,function(a){var e=t.get("checkoutProvider");if(e.set("params.invalid",!1),e.trigger("mageworxShippingFeeForm.data.validate"),e.get("params.invalid"))g.addErrorMessage({message:errorMessage});else{var r=e.get("mageworxShippingFeeForm");if(r){r.type=3;var i=n(r,function(){},"mageworx-shipping-fee-form");if(i)return i.always(function(){a()})}}return a()})}});
