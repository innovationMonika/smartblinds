define([
    'jquery',
    'underscore',
    'knockout'
], function (
    $,
    _,
    ko
) {
    'use strict';

    return function (Component) {
        return Component.extend({

            getConversionParam: function (p) {
                let match = RegExp('[?&]' + p + '=([^&]*)').exec(window.location.search);
                return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
            },

            getExpiryRecord: function(value) {
                let expiryPeriod = 90 * 24 * 60 * 60 * 1000, // 90 day expiry in milliseconds
                    expiryDate = new Date().getTime() + expiryPeriod;
                return {
                    value: value,
                    expiryDate: expiryDate
                };
            },

            addGclid: function() {
                let gclidRecord  = null,
                    gclidParam = this.getConversionParam('gclid'),
                    gclsrcParam = this.getConversionParam('gclsrc'),
                    isGclsrcValid = !gclsrcParam || gclsrcParam.indexOf('aw') !== -1;

                if (gclidParam && isGclsrcValid) {
                    gclidRecord = this.getExpiryRecord(gclidParam);
                    localStorage.setItem('gclid', JSON.stringify(gclidRecord));
                }

                let gclid = gclidRecord || JSON.parse(localStorage.getItem('gclid')),
                    isGclidValid = gclid && new Date().getTime() < gclid.expiryDate;

                return isGclidValid ? gclid?.value : null;
            },

            addFbp: function () {
                if ($.cookie('_fbp') === undefined) {
                    return null;
                }
                return $.cookie('_fbp');
            },

            addFbc: function () {
                let _fbcParam = this.getConversionParam('fbclid');
                if (_fbcParam) {
                    $.cookie("_fbc", _fbcParam);
                    return _fbcParam;
                }
                if ($.cookie('_fbc') === undefined) {
                    return null;
                }
                return $.cookie('_fbc');
            },

            setFormData: function (section) {
                this._super();
                this.formData.gclid = ko.observable(this.addGclid() ?? null);
                this.formData.fbp = ko.observable(this.addFbp() ?? null);
                this.formData.fbc = ko.observable(this.addFbc() ?? null);
            }
        });
    }
});
