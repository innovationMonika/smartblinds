define([
  "jquery",
  "underscore",
  "getSwatchSelectedProductId",
  "priceUtils",
  "mage/translate",
  "Smartblinds_Options/js/price-option/width-height/calculations",
  "Smartblinds_Options/js/model/price-calculator",
  "Smartblinds_Options/js/model/system",
  "Smartblinds_Options/js/model/option-params",
  "mage/validation",
  "priceOptions",
  "jquery-ui-modules/widget",
], function (e, $, w, C, c, y, D, p, v) {
  "use strict";
  var u = e(window),
    I = {},
    V = !1,
    _ = !1;
  return (
    e.widget("mage.priceOptionWidthHeight", {
      options: {
        formSelector: "#product_addtocart_form",
        valueFieldSelector: "input[type=hidden]",
        widthFieldSelector: "input[data-role=width]",
        heightFieldSelector: "input[data-role=height]",
        systemTypeSelector: "input[data-attr-name=system_type]",
        controlTypeSelector: "input[data-attr-name=control_type]",
        systemSizeSelector: "input[data-attr-name=system_size]",
        fabricSizeSelector: "input[data-attr-name=fabric_size]",
        progressButtonSelector: "[data-role=progress-button]",
        motorSelector: ".selection_motor_venetian input",
      },
      _create: function () {
        this._initFields()
          ._initEvents()
          ._initOptionHandler()
          ._addValidatorMethod();
      },
      _initFields: function () {
        return (
          (this.form = this.element.closest(this.options.formSelector)),
          (this.$labelValue = e(this.element).find("#value")),
          (this.$form = e(this.form)),
          (this.$valueField = e(
            this.element.find(this.options.valueFieldSelector)
          )),
          (this.$widthField = e(
            this.element.find(this.options.widthFieldSelector)
          )),
          (this.$heightField = e(
            this.element.find(this.options.heightFieldSelector)
          )),
          (this.$progressButton = e(
            this.$form.find(this.options.progressButtonSelector)
          )),
          (this.$motorRadio = e(this.$form.find(this.options.motorSelector))),
          (this.$motorRadioValue = e(
            this.element.find(this.options.motorSelector)
          )),
          p.init(this.$valueField),
          v.init(this.$valueField),
          this
        );
      },
      _initEvents: function () {
        return (
          this.$motorRadio.on("change", this._updateValueFieldMotor.bind(this)),
          this.$motorRadio.on("change", this._updateValueField.bind(this)),
          this.$motorRadio.on("click", this._validateField.bind(this)),
          this.$motorRadio.on("focusout", this._sendUpdateEvent.bind(this)),
          this.$widthField.on("input", this._updateValueField.bind(this)),
          this.$widthField.on("focusout", this._validateField.bind(this)),
          this.$heightField.on("input", this._updateValueField.bind(this)),
          this.$heightField.on("focusout", this._validateField.bind(this)),
          this.$widthField.on("focusout", this._sendUpdateEvent.bind(this)),
          this.$heightField.on("focusout", this._sendUpdateEvent.bind(this)),
          u.on("swatches-click", this._updateValueField.bind(this)),
          u.on("swatches-click", this._addValidatorWidthHeight.bind(this)),
          u.on("swatches-click", this._handleSystemSizeAvailability.bind(this)),
          this.$progressButton.on(
            "mousedown",
            this._updateValueField.bind(this)
          ),
          this.$progressButton.on("mouseup", this._validateField.bind(this)),
          u.on(
            "priceOptionWidthHeightUpdate",
            this._updateValueField.bind(this)
          ),
          u.on(
            "priceOptionWidthHeightValidate",
            this._validateField.bind(this)
          ),
          u.on(
            "priceOptionWidthHeightSendUpdate",
            this._sendUpdateEvent.bind(this)
          ),
          e("body").hasClass("checkout-cart-configure") &&
            (this.$valueField.addClass("selected"), u.trigger("update-steps")),
          this
        );
      },
      _updateValueFieldMotor: function (t) {
        var r = e(t.currentTarget || t);
        if (r.is(":checked")) {
          var i = r.data("width") || "",
            o = r.data("height") || "",
            n = r.data("m2") || "";
          i === ""
            ? this.$widthField.attr(
                "placeholder",
                e("#default_width").attr("placeholder")
              )
            : this.$widthField.attr("placeholder", i),
            o === ""
              ? this.$heightField.attr(
                  "placeholder",
                  e("#default_height").attr("placeholder")
                )
              : this.$heightField.attr("placeholder", o),
            n === ""
              ? this.$widthField.attr("m2", e("#default_width").attr("m2"))
              : this.$widthField.attr("m2", n),
            this._addValidatorMethod();
        }
      },
      _onCustomOptionChange: function (t) {
        this._updateValueField(t),
          this._validateField(t),
          this._sendUpdateEvent(t);
      },
      _initOptionHandler: function () {
        var t = this.$valueField.data("role"),
          r = {optionHandlers: {}};
        return (
          (r.optionHandlers[t] = this._optionHandler.bind(this)),
          this.form.priceOptions(r),
          this
        );
      },
      _prepareInputValue: function (t) {
        let r = t.val().replace(",", ".");
        return (r = r || 0), parseInt(parseFloat(r) * 10);
      },
      _prepareInputPlaceholderValue: function (t, r) {
        let i = t.attr("placeholder").replace(",", ".");
        return (i = i || 0), parseInt(parseFloat(i) * 10);
      },
      _prepareInputM2Value: function (t, r) {
        let i = t.attr("m2");
        return (i = i || 0), i;
      },
      _isChainProduct: function () {
        let t = new URLSearchParams(window.location.hash.substring(1)),
          r = t.get("sku") ? t.get("sku").toLowerCase() : null;
        return r && r.includes("chain");
      },
      _updateValueField: function (t, r, i) {
        if (i) return;
        const o = {
          width: this._prepareInputValue(this.$widthField),
          height: this._prepareInputValue(this.$heightField),
          matchwidth: this._prepareInputPlaceholderValue(
            this.$widthField,
            "width"
          ),
          matchheight: this._prepareInputPlaceholderValue(
            this.$heightField,
            "height"
          ),
          matchm2: this._prepareInputM2Value(this.$widthField, "width"),
        };
        this.$valueField.val(JSON.stringify(o)),
          (o.system_type = e(
            this.$form.find(this.options.systemTypeSelector)
          ).val()),
          e(this.$form.find(this.options.controlTypeSelector)).length > 0
            ? (o.control_type = e(
                this.$form.find(this.options.controlTypeSelector)
              ).val())
            : (o.control_type = this._isChainProduct() ? "chain" : "motor"),
          (o.system_size = e(
            this.$form.find(this.options.systemSizeSelector)
          ).val()),
          $.each(
            o,
            function (s, h) {
              this.$valueField.data(h, s);
            },
            this
          ),
          this._updatePlaceholders(o);
        const n = o.width / 10,
          l = o.height / 10;
        Number.isInteger(o.width) &&
          Number.isInteger(o.height) &&
          this.$labelValue.text(n + " x " + l + " cm");
        var d = p.get(),
          a = window.jsonConfig.systemTypeValues;
        a[d.systemType] == "tdbu"
          ? e(
              '.product-option-info-icon[data-option-modal="product_option_modal_motor_side"]'
            )
              .eq(0)
              .closest(".product-option")
              .addClass("motor_side-visible-0")
          : e(
              '.product-option-info-icon[data-option-modal="product_option_modal_motor_side"]'
            )
              .eq(0)
              .closest(".product-option")
              .removeClass("motor_side-visible-0"),
          this.$valueField.trigger("change");
      },
      _sendUpdateEvent: function () {
        var t = this.$valueField.data("width"),
          r = this.$valueField.data("height");
        if (
          Number.isInteger(t) &&
          Number.isInteger(r) &&
          this.$widthField.valid()
        ) {
          var i = t / 10,
            o = r / 10;
          u.trigger("option-width-height-change", [i, o]);
        }
      },
      _validateField: function (t) {
        if (this.$widthField.is(":focus") || this.$heightField.is(":focus")) {
          this.$valueField.removeClass("selected");
          return;
        }
        this.$widthField.val(this.$widthField.val().replace(",", ".")),
          this.$heightField.val(this.$heightField.val().replace(",", ".")),
          this._handleSystemSizeAvailability(),
          this.$widthField.val() &&
          this.$heightField.val() &&
          this.$widthField.valid()
            ? (this.$valueField.addClass("selected"), u.trigger("update-steps"))
            : this.$valueField.removeClass("selected");
      },
      _handleSystemSizeAvailability: function () {
        const t = e(this.$form.find(this.options.systemSizeSelector)).parent();
        t.find("[data-option-id]").attr("disabled", !1).prop("disabled", !1);
        const r = window.jsonConfig.systemSizeValues,
          i = $.invert(r),
          o = i.small,
          n = i.medium;
        let l = p.get();
        if (!l) return;
        let d = null,
          a = parseInt(w());
        if (r[l.systemSize] === "medium") {
          if (_ === !0) return;
          (_ = !0),
            t.find('[data-option-id="' + o + '"]').trigger("click"),
            (a = parseInt(w())),
            (d = p.get()),
            t.find('[data-option-id="' + n + '"]').trigger("click"),
            (_ = !1);
        }
        const s = v.get(a, d);
        if (!s) return;
        l = s.system;
        const h = parseFloat(this.$valueField.data("width")),
          m = parseFloat(this.$valueField.data("height")),
          f = y.calcMaxWidth(s),
          F = y.calcMaxHeight(s);
        (h > f ||
          m > F ||
          (parseFloat(s.product.thickness) >= 0.5 && h >= 2e3)) &&
          (r[l.systemSize] === "small" &&
            t.find('[data-option-id="' + n + '"]').trigger("click"),
          t
            .find('[data-option-id="' + o + '"]')
            .attr("disabled", !0)
            .prop("disabled", !0));
      },
      _updatePlaceholders: function (t) {
        var r = p.get();
        if (!r) return;
        const i = window.jsonConfig.systemsPlaceholder[r.id];
        i.widthPlaceHolder !== "" &&
          this.$widthField.attr("placeholder", i.widthPlaceHolder),
          i.heightPlaceHolder !== "" &&
            this.$heightField.attr("placeholder", i.heightPlaceHolder);
      },
      _optionHandler: function (t, r) {
        var i = C.findOptionId(t),
          o = r[i] ? r[i].prices : void 0,
          n = "options[" + i + "]",
          l = {};
        const d = D.calculateWidthHeightFinalPrice();
        return o == null
          ? ((l[n] = {}), l)
          : d === null
          ? ((l[n] = {}), l)
          : ((o.basePrice.amount = d),
            (o.finalPrice.amount = d),
            (o.oldPrice.amount = d),
            (o.oldPrice.amount_excl_tax = d),
            (o.oldPrice.amount_incl_tax = d),
            (l[n] = o),
            l);
      },
      _addValidatorWidthHeight: function () {
        let t = this.$valueField.data("width"),
          r = this.$valueField.data("height");
        if (t && r) {
          let i = p.getByType("motor"),
            o = p.getByType("chain");
          i && t < i.minWidth && i.isChainCustomerGroup == 1
            ? (e(
                "#option-label-control_type-" +
                  i.controlTypeData.attributeId +
                  "-item-" +
                  i.controlTypeData.options.chain
              ).removeClass("disabled"),
              e(
                "#option-label-control_type-" +
                  i.controlTypeData.attributeId +
                  "-item-" +
                  i.controlTypeData.options.motor
              ).addClass("disabled"))
            : o &&
              t > o.maxWidth &&
              (e(
                "#option-label-control_type-" +
                  i.controlTypeData.attributeId +
                  "-item-" +
                  i.controlTypeData.options.motor
              ).removeClass("disabled"),
              e(
                "#option-label-control_type-" +
                  i.controlTypeData.attributeId +
                  "-item-" +
                  i.controlTypeData.options.chain
              ).addClass("disabled"));
        }
      },
      _addValidatorMethod: function () {
        var t = this;
        return (
          (e.validator.messages = {
            required: c(
              "Don't forget to fill in the width and height of your window recess here"
            ),
          }),
          e.validator.addMethod(
            "width-height",
            function (r, i) {
              var o = v.get(),
                n = e(i),
                l = p.getByType("motor"),
                d = p.getByType("chain");
              if (!t.$valueField.data("width") || !t.$valueField.data("height"))
                return t.$progressButton.hasClass("progress-btn")
                  ? (n.data(
                      "error-message",
                      c(
                        "Don't forget to fill in the width and height of your window recess here"
                      )
                    ),
                    !1)
                  : !0;
              if (!o)
                return (
                  n.data("error-message", c("This product is unavailable")), !1
                );
              var a = o.system,
                s = t.$valueField.data("width"),
                h = t.$valueField.data("height"),
                m = t.$valueField.data("matchwidth"),
                f = t.$valueField.data("matchheight"),
                F = ((s / 100) * (h / 100)).toFixed(2),
                T = F / 100,
                S = t.$valueField.data("matchm2"),
                g = y.calcMaxWidth(o),
                b = y.calcMaxHeight(o);
              if (!h || !s) return !1;
              if (
                h / s > 3 &&
                o.system.systemCategory !== "honeycomb_blinds" &&
                o.system.systemCategory !== "venetian_blinds"
              )
                return (
                  n.data(
                    "error-message",
                    c(
                      "Your product has a width/height ratio greater than 1:3. This is greater than our recommended ratio. Choose dimensions that fall within this ratio."
                    )
                  ),
                  !1
                );
              if (
                e(t.$form.find(t.options.controlTypeSelector)).length > 0 &&
                s < l.minWidth &&
                a.controlTypeData.options[a.controlType] != "chain" &&
                a.isChainCustomerGroup == 1
              )
                e(
                  "#option-label-control_type-" +
                    a.controlTypeData.attributeId +
                    "-item-" +
                    a.controlTypeData.options.chain
                ).removeClass("disabled"),
                  e(
                    "#option-label-control_type-" +
                      a.controlTypeData.attributeId +
                      "-item-" +
                      a.controlTypeData.options.chain
                  ).trigger("click"),
                  e(
                    "#option-label-control_type-" +
                      a.controlTypeData.attributeId +
                      "-item-" +
                      a.controlTypeData.options.motor
                  ).addClass("disabled"),
                  console.log(
                    "De gekozen breedte van " +
                      s / 10 +
                      " cm is alleen mogelijk met een ketting bediend systeem."
                  );
              else if (
                e(t.$form.find(t.options.controlTypeSelector)).length > 0 &&
                a.controlTypeData.options[a.controlType] == "chain" &&
                s > d.maxWidth
              )
                e(
                  "#option-label-control_type-" +
                    a.controlTypeData.attributeId +
                    "-item-" +
                    a.controlTypeData.options.motor
                ).removeClass("disabled"),
                  e(
                    "#option-label-control_type-" +
                      a.controlTypeData.attributeId +
                      "-item-" +
                      a.controlTypeData.options.motor
                  ).trigger("click"),
                  e(
                    "#option-label-control_type-" +
                      a.controlTypeData.attributeId +
                      "-item-" +
                      a.controlTypeData.options.chain
                  ).addClass("disabled"),
                  console.log(
                    "De gekozen breedte van " +
                      s / 10 +
                      " cm is niet mogelijk met een ketting bediend systeem."
                  );
              else {
                if (s > g && h > b)
                  return (
                    n.data(
                      "error-message",
                      c(
                        "Your product is too broad and therefore cannot be delivered. The maximum width for a product of %1 cm high is: %2 cm. Try a different fabric or choose different dimensions."
                      )
                        .replace("%1", h / 10)
                        .replace("%2", g / 10)
                    ),
                    !1
                  );
                if (s <= g && h > b)
                  return (
                    n.data(
                      "error-message",
                      c(
                        "Your product is too high and therefore cannot be delivered. The maximum height for a product of %1 cm wide is: %2 cm. Try a different fabric or choose different dimensions."
                      )
                        .replace("%1", s / 10)
                        .replace("%2", b / 10)
                    ),
                    !1
                  );
                if (s > g && h <= b)
                  return (
                    n.data(
                      "error-message",
                      c(
                        "Your product is too broad and therefore cannot be delivered. The maximum width for a product of %1 cm high is: %2 cm. Try a different fabric or choose different dimensions."
                      )
                        .replace("%1", h / 10)
                        .replace("%2", g / 10)
                    ),
                    !1
                  );
                if (f > h || m > s)
                  return (
                    n.data(
                      "error-message",
                      c(
                        "Your product is too small and therefore cannot be delivered. The minimum width is %1 cm and the minimum height is %2 cm."
                      )
                        .replace("%1", m / 10)
                        .replace("%2", f / 10)
                    ),
                    !1
                  );
                if (
                  (a.minHeight > h && f >= a.minHeight) ||
                  (a.minWidth > s && m >= a.minWidth)
                )
                  return (
                    n.data(
                      "error-message",
                      c(
                        "Your product is too small and therefore cannot be delivered. The minimum width is %1 cm and the minimum height is %2 cm."
                      )
                        .replace("%1", a.minWidth / 10)
                        .replace("%2", a.minHeight / 10)
                    ),
                    !1
                  );
                if (T > S && o.system.systemCategory == "venetian_blinds")
                  return (
                    n.data(
                      "error-message",
                      c(
                        "Het maximale aantal m2 voor deze selectie is %1. De door jou ingevulde maten hebben een oppervlakte van %2 m2. Kies een ander type motor of neem contact met ons op voor de mogelijkheden."
                      )
                        .replace("%1", S)
                        .replace("%2", T)
                    ),
                    !1
                  );
                l && s < l.minWidth && a.isChainCustomerGroup == 1
                  ? (e(
                      "#option-label-control_type-" +
                        a.controlTypeData.attributeId +
                        "-item-" +
                        a.controlTypeData.options.chain
                    ).removeClass("disabled"),
                    e(
                      "#option-label-control_type-" +
                        a.controlTypeData.attributeId +
                        "-item-" +
                        a.controlTypeData.options.motor
                    ).addClass("disabled"))
                  : d &&
                    s > d.maxWidth &&
                    (e(
                      "#option-label-control_type-" +
                        a.controlTypeData.attributeId +
                        "-item-" +
                        a.controlTypeData.options.motor
                    ).removeClass("disabled"),
                    e(
                      "#option-label-control_type-" +
                        a.controlTypeData.attributeId +
                        "-item-" +
                        a.controlTypeData.options.chain
                    ).addClass("disabled"));
              }
              return !0;
            },
            function (r, i) {
              return e(i).data("error-message");
            }
          ),
          this
        );
      },
    }),
    e.mage.priceOptionWidthHeight
  );
});
