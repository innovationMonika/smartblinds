define(["jquery","mage/mage"],function(i){"use strict";return function(t){var a=i("#"+t.formId),d=i('<input type="hidden">').attr({id:"newsletter_page",name:"page",value:window.location.pathname});a.append(d),a.submit(function(){var e=i(this);e.find(":submit").attr("disabled","disabled"),this.isValid===!1&&e.find(":submit").prop("disabled",!1),this.isValid=!0}),a.bind("invalid-form.validate",function(){i(this).find(":submit").prop("disabled",!1),this.isValid=!1})}});
