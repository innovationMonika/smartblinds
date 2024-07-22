define([
    'underscore',
    'jquery',
    'mage/translate',
    'uiComponent',
    'sampleBasket',
    'mage/apply/main',
    'Magento_Customer/js/customer-data',
    'knockout',
    'uuidv4.min',
    'mage/validation',
    'mage/cookies'
], function(
    _,
    $,
    $t,
    Component,
    basket,
    mage,
    customerData,
    ko,
    uuidv4
) {
    'use strict';

    var $window = $(window);
    var sendDatas;

    return Component.extend({
        initialize: function () {
            this.urls = {};
            this.formId = null;
            this._super()
                .initElements()
                .initVariables()
                .initEvents();

            this.setFormData(customerData.get('customer')());

            return this;
        },

        initObservable: function () {
            return this
                ._super()
                .observe([
                    'items',
                    'createAccount',
                    'customerEmail',
                    'errorMessage',
                    'successMessage',
                    'isLoading',
                    'country',
                    'prefix',
                    'isSubscribed'
                ].join(' '));
        },

        initElements: function () {
            this.$form = $(this.selectors.form);
            this.$password = $(this.selectors.password);
            return this;
        },

        initVariables: function () {
            this.updateBasketItems();
            this.initFormDefaults();
            return this;
        },

        initEvents: function () {
            $window.on('sample-basket-updated', this.updateBasketItems.bind(this));
            customerData.get('customer').subscribe(this.setFormData.bind(this));
            $window.on("beforeunload", this.updateFormData.bind(this));
            return this;
        },

        updateBasketItems: function () {
            this.items(basket.getSimpleItems());
        },

        initFormDefaults: function () {
            this.country(this.defaults.countryId);
            this.prefix(this.defaults.prefix);
            this.createAccount(false);
            this.isSubscribed(false);
            this.customerEmail(null);
        },
        updateFormData: function () {
            let serializedForm = JSON.stringify($("#sampleFormId").serializeArray());
            let expireTime = new Date(new Date().getTime() + 86400);
            $.mage.cookies.set('sample_form_data', serializedForm, {expires: expireTime, domain: ""});
        },
        setFormData: function (section) {
            if (!section) {
                section = {};
            }

            this.customerEmail(section.email);
            var sectionFormData = section.hasOwnProperty('samplesFormData') ?
                section.samplesFormData : {};
            if (sectionFormData.country) {
                this.country(sectionFormData.country);
            }
            if (sectionFormData.prefix) {
                this.prefix(sectionFormData.prefix);
            }

            let cookieData = $.mage.cookies.get('sample_form_data');
            if (cookieData) {
                cookieData = JSON.parse(cookieData);
                let thz = this;
                _.each(cookieData, function (value, key) {
                    if (!sectionFormData[value.name] || sectionFormData[value.name].length === 0) {
                        sectionFormData[value.name] = value.value;
                    }
                    if (value.name === 'is_subscribed' && value.value === 'on') {
                        thz.isSubscribed(true);
                    }
                });
            }

            var formData = {
                'postcode': sectionFormData.postcode,
                'house': sectionFormData.house,
                'apartment': sectionFormData.apartment,
                'street': sectionFormData.street,
                'city': sectionFormData.city,
                'firstname': sectionFormData.firstname,
                'middlename': sectionFormData.middlename,
                'lastname': sectionFormData.lastname,
                'telephone': sectionFormData.telephone,
                'email': sectionFormData.email
            };
            if (this.formData) {
                _.each(formData, function (value, key) {
                    this.formData[key](value);
                }.bind(this));
                return;
            }
            this.formData = {};
            _.each(formData, function (value, key) {
                this.formData[key] = ko.observable(value);
            }.bind(this));
        },

        onSubmit: function () {
            if (this.isLoading()) {
                return;
            }

            this.setValidationMessages(this.$form);

            if (this.$form.valid()) {
                this.prepareFormSubmit();
                this.submitForm();
                $.mage.cookies.clear('sample_form_data');
            }
        },

        prepareFormSubmit: function () {
            this.errorMessage('');
            $(this.$form[0]).find('.valid').removeClass('valid');
            this.isLoading(true);
            this.formId = uuidv4();
        },

        submitForm: function () {
            sendDatas = this.getSendData();
            $.ajax({
                type: 'POST',
                url: this.urls.placeOrder,
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({info: sendDatas}),
                success: this.handleSuccessResponse.bind(this),
                error: this.handleErrorResponse.bind(this),
            });
        },

        getSendData: function () {
            var data = _.mapObject(this.formData, function (observable) {
                return observable() ? observable() : null;
            });
            data['form_id'] = this.formId;
            data['country_id'] = this.country();
            data['prefix'] = this.prefix() ? this.prefix() : null;
            data['create_account'] = this.createAccount();
            if (this.createAccount()) {
                data['password'] = this.$password.find('#password').val()
            }
            data.items = _.map(this.items(), function (obj) {
                return _.pick(obj, 'id', 'name')
            });
            return data;
        },

        handleSuccessResponse: function (response) {
            this.isLoading(false);
            this.afterSubmit();

            if (!response.success) {
                this.errorMessage(response.message);
                return;
            }

            this.resetForm();

            var message = $t('Your samples claim required has been accepted. Order ID: %1')
                .replace('%1', response.message);
            this.successMessage(message)
            this.errorMessage('');

            this.formId = null;
            basket.clear();
            if (sendDatas && sendDatas.email) {
                dataLayer.push({
                    'email': sendDatas.email,
                    'event': 'fireEnhancedConversionTag'
                });
                if (typeof _paq !== 'undefined') {
                    _paq.push(['trackGoal', 1]);
                }
            }
            customerData.invalidate(['customer']);
        },

        handleErrorResponse: function () {
            this.isLoading(false);
            this.errorMessage($t('Unexpected error has occurred. Please try again later.'));
        },

        afterSubmit: function () {
            var items = [];

            items = _.map(this.items(), function (obj) {
                return {name: obj.name};
            });

            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    'event': 'Sample Request',
                    'eventCategory': 'lead',
                    'eventAction': 'sample_request',
                    'eventLabel': JSON.stringify(items),
                    'sampleFormId': this.formId
                });
            }
        },

        onRender: function () {
            this.$form.validate();
            if (this.$password.length) {
                mage.applyFor(this.$password, {}, 'passwordStrengthIndicator');
            }
        },

        resetForm: function () {
            this.initFormDefaults();
            _.each(this.formData, function (value, key) {
                value(null);
            }.bind(this));
        },

        setValidationMessages: function (form) {
            var fields = [
                {
                    field: '#firstname',
                    messages: {
                        required: $t('Do not forget to fill in your first name')
                    }
                },
                {
                    field: '#lastname',
                    messages: {
                        required: $t("Don't forget to fill in your surname")
                    }
                },
                {
                    field: '#email',
                    messages: {
                        required: $t("Don't forget to fill in your e-mail address")
                    }
                },
                {
                    field: '#postcode',
                    messages: {
                        required: $t("Don't forget to fill in your postal code")
                    }
                },
                {
                    field: '#house',
                    messages: {
                        required: $t("Do not forget to fill in your house number")
                    }
                },
                {
                    field: '#street',
                    messages: {
                        required: $t("Don't forget to fill in your street name")
                    }
                },
                {
                    field: '#city',
                    messages: {
                        required: $t("Don't forget to fill in your city of residence")
                    }
                },
            ];

            $.each(fields, function (index, value) {
                form.find(value.field).rules('add', {
                    messages: value.messages
                });
            });
        }
    });

});
