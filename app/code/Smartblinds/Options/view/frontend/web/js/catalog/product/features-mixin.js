define(["jquery", "underscore", "Magento_Catalog/js/price-utils", "getSwatchSelectedProductId", "priceUtils"], function(t, y, O, w, C) {
    "use strict";
    var E = {
        calculateSelectedOptionsPrice: function() {
            this._super();
            var e = this,
                p = this.base.getFormElement(),
                c = this.base.options,
                n = t(c.optionsSelector, p);
            n.filter('input[type="hidden"]').each(function(h, r) {
                var i = t(r),
                    o = O.findOptionId(i),
                    d = c.optionConfig && c.optionConfig[o],
                    m = i.val();
                if (i.closest(".field").css("display") === "none") {
                    i.val("");
                    return
                }
                var s = t(c.productQtySelector).val(),
                    f = i.closest("#product_addtocart_form"),
                    u = i.data("role"),
                    x = f.data("mage-priceOptions").options.optionHandlers[u],
                    _ = {};
                if (x) {
                    var g = {};
                    g[o] = d, _ = x(i, g)
                }
                var P = _.hasOwnProperty(i.data("role")) ? _[i.data("role")] : null,
                    a = P || d.prices,
                    T = a.basePrice.amount,
                    b = a.finalPrice.amount;
                e.optionFinalPrice += parseFloat(b) * s, e.optionOldPriceInclTax += parseFloat(a.oldPrice.amount_incl_tax) * s, e.optionBasePrice += parseFloat(T) * s, e.optionOldPriceExclTax += parseFloat(a.oldPrice.amount_excl_tax) * s, e.optionFinalPricePerItem += parseFloat(b), e.optionOldPricePerItemInclTax += parseFloat(a.oldPrice.amount_incl_tax), e.optionBasePricePerItem += parseFloat(T), e.optionOldPricePerItemExclTax += parseFloat(a.oldPrice.amount_excl_tax)
            })
        },
        collectOptionPriceAndQty: function(e, p, c) {
            var n;
            this.actualPriceInclTax = 0, this.actualPriceExclTax = 0;
            var h = this.base.options,
                r = this.base.isOneTimeOption(p),
                i = t(h.productQtySelector).val(),
                o = y.isUndefined(e.qty) ? 1 : e.qty;
            this.getActualPrice(p, c, o), i == 0 && (i = 1);
            var d = this.actualPriceInclTax ? this.actualPriceInclTax : parseFloat(e.prices.finalPrice.amount),
                m = this.actualPriceExclTax ? this.actualPriceExclTax : parseFloat(e.prices.basePrice.amount),
                s = parseFloat(e.prices.oldPrice.amount_incl_tax),
                f = parseFloat(e.prices.oldPrice.amount_excl_tax),
                u = this.actualPriceInclTax ? this.actualPriceInclTax : parseFloat(e.prices.finalPrice.amount),
                x = this.actualPriceExclTax ? this.actualPriceExclTax : parseFloat(e.prices.basePrice.amount),
                _ = parseFloat(e.prices.oldPrice.amount_incl_tax),
                g = parseFloat(e.prices.oldPrice.amount_excl_tax);
            const P = jsonConfig.optionPrices[w()];
            if (P && P.oldPrice.amount > 0) {
                const l = P.finalPrice.amount / P.oldPrice.amount;
                u *= l, x *= l, d *= l, m *= l
            }!r && (this.options.product_price_display_mode === "final_price" || this.options.additional_product_price_display_mode === "final_price") && (d *= i, m *= i, s *= i, f *= i);
            const a = t('.mageworx-swatch-option.selected[data-option-id="' + p + '"][data-option-type-id="' + c + '"]').first(),
             T = a.closest(".product-option").find("span#doubletext");
         if (t(".selection_bottombar .mageworx-swatch-option.image.selected") && t(".width-height-option .input-text.product-custom-option").hasClass('selected')) {
           t(".selection_clamp").show();
         }
            if (t(".selection_bottombar").css("display") !== "none" && t("body").hasClass("smallsystem")) {
              t(".selection_clamp").show();
            }
            if (a.parents(".product-option").hasClass("selection_bottombar") && t("body").hasClass("smallsystem") && t(".width-height-option .input-text.product-custom-option").hasClass('selected')) {
                var b = t(".selection_side_span"),
                    F = t(".selection_side_span .options-list .radio"),
                    j = a.parent().data("value");
                j == "rc3032" ? (b.hide(), F.prop("disabled", !0).hide()) : (b.show(), F.prop("disabled", !1).show());
                var I = t(".selection_clamp"),
                    v = t(".selection_clamp .options-list .radio"),
                    S = a.parent().data("value");
                 I.show();
            }
         if (a.parents(".product-option").hasClass("selection_bottombar") && !t("body").hasClass("smallsystem")) {
          var b = t(".selection_side_span"), I = t(".selection_clamp");
          b.hide();
          I.hide();
         }
         else if(!t("body").hasClass("smallsystem")) {
          var b = t(".selection_side_span"), I = t(".selection_clamp");
          b.hide();
          I.hide();
         }
            a.closest(".product-option").find("span#value").removeClass("with-doubletext"), T.length && T.remove();
            const R = a.data("option-code") === window.jsonConfig.bedieningOptionCode,
                B = (n = Object.values(window.jsonConfig.attributes).find(l => l.code === "system_type")) == null ? void 0 : n.id,
                D = t('.swatch-attribute[data-attribute-id="' + B + '"]').find('.swatch-option.selected[data-option-id="' + window.jsonConfig.systemTypeTdbuOptionId + '"]').length > 0;
            if (R && D) {
                u *= 2, x *= 2, _ *= 2, g *= 2, d *= 2, m *= 2, s *= 2, f *= 2;
                const l = a.closest(".product-option").find("span#value");
                if (l.length && u > 0) {
                    const q = C.formatPrice(u, window.jsonConfig.currencyFormat);
                    let Q = a.data("option-label");
                    l.addClass("with-doubletext").append('<span id="doubletext">' + Q + " +" + q + "</span>")
                }
            }
            this.optionFinalPricePerItem += u * o, this.optionBasePricePerItem += x * o, this.optionOldPricePerItemInclTax += _ * o, this.optionOldPricePerItemExclTax += g * o, this.optionFinalPrice += d * o, this.optionBasePrice += m * o, this.optionOldPriceInclTax += s * o, this.optionOldPriceExclTax += f * o
        },
        initProductPrice: function(e) {
            if (!this.swatchesNotSubscribed) {
                var p = t('div[data-role="swatch-options"]');
                p.on("swatch.initialized", () => {
                    t(window).trigger("swatches-click")
                }), this.swatchesNotSubscribed = !0
            }
            let c = w(),
                n = window.jsonConfig,
                h = (n == null ? void 0 : n.optionPrices) ? n.optionPrices : {},
                r = h.hasOwnProperty(c) ? h[c] : null;
            r && (e.regular_price_excl_tax = r.baseOldPrice.amount, e.regular_price_incl_tax = r.oldPrice.amount, e.final_price_excl_tax = r.basePrice.amount, e.final_price_incl_tax = r.finalPrice.amount), this.productDefaultRegularPriceExclTax = e.regular_price_excl_tax, this.productDefaultRegularPriceInclTax = e.regular_price_incl_tax, this.productDefaultFinalPriceExclTax = e.final_price_excl_tax, this.productDefaultFinalPriceInclTax = e.final_price_incl_tax, this.productPerItemRegularPriceExclTax = e.regular_price_excl_tax, this.productPerItemRegularPriceInclTax = e.regular_price_incl_tax, this.productPerItemFinalPriceExclTax = e.final_price_excl_tax, this.productPerItemFinalPriceInclTax = e.final_price_incl_tax, this.productTotalRegularPriceExclTax = e.regular_price_excl_tax, this.productTotalRegularPriceInclTax = e.regular_price_incl_tax, this.productTotalFinalPriceExclTax = e.final_price_excl_tax, this.productTotalFinalPriceInclTax = e.final_price_incl_tax
        }
    };
    return function(e) {
        return t.widget("mageworx.optionFeatures", e, E), t.mageworx.optionFeatures
    }
});