define(["jquery", "underscore", "priceUtils", "mage/translate", "Smartblinds_Options/js/model/price-calculator", "mage/validation", "priceOptions", "jquery-ui-modules/widget"], function(t, h, u, d, s) {
  "use strict";
  var m = t(window);
  return t.widget("mage.priceOptionCurtainTracksWidth", {
    options: {
      formSelector: "#product_addtocart_form",
      widthFieldSelector: "input[data-role=curtain_tracks_width]"
    },
    _create: function() {
      this._initFields()._initEvents()._addValidatorMethod()._initOptionHandler();
    },
    _initFields: function() {
      return this.form = this.element.closest(this.options.formSelector), this.$form = t(this.form), this.$widthField = t(this.element.find(this.options.widthFieldSelector)), s.init(this.$widthField), this;
    },
    _initEvents: function() {
      return this.$widthField.on("focusout", this._validateField.bind(this)), this;
    },
    _validateField: function(n) {
      t.validator.validateSingleElement(t(this.$widthField), {});
    },
    _initOptionHandler: function() {
      var n = "curtain_tracks_width", i = {
        optionHandlers: {}
      };
      return i.optionHandlers[n] = this._optionHandler.bind(this), this.form.priceOptions(i), this;
    },
    _optionHandler: function(n, i) {
      var r = u.findOptionId(n), e = i[r].prices, a = "options[" + r + "]", o = {};
      let c = s.calculateCurtainTracksFinalPrice();
      if (c === null)
        return o[a] = {}, o;
      const l = s.calculateCurtainTracksRegularPrice();
      if (window.curtainTracks && window !== null) {
        let P = t(".mageworx-swatch-container .mageworx-swatch-option");
        if (P.length > 0) {
          let T = this;
          P.each(function() {
            let k = t(this);
            if (!k.hasClass("selected")) {
              return;
            }
            let d2 = k.attr("data-option-price");
            if (!d2) {
              return;
            }
            d2 = parseFloat(d2);
            if (!isNaN(d2)) { // Check if d2 is a valid number
              c += d2;
            }
          });
          // You may want to do something with 'c' after the loop
        }
      }
      return e.basePrice.amount = c, e.finalPrice.amount = c, e.oldPrice.amount = l, e.oldPrice.amount_excl_tax = l, e.oldPrice.amount_incl_tax = l, o[a] = e, o;
    },
    _addValidatorMethod: function() {
      var n = this;
      return t.validator.addMethod("curtain-tracks", function(i, r) {
        const e = t(r);
        if (i = i.replace(",", "."), isNaN(i))
          return e.data("error-message", d("Wrong width provided")), false;
        const a = parseInt(i);
        return a < 50 ? (e.data("error-message", d("Your product is too small. The minimum width is 50 cm.")), false) : a > 580 ? (e.data("error-message", d("Your product is too broad and therefore cannot be delivered. The maximum width is 580 cm.")), false) : true;
      }, function(i, r) {
        return t(r).data("error-message");
      }), this;
    }
  }), t.mage.priceOptionCurtainTracksWidth;
});
