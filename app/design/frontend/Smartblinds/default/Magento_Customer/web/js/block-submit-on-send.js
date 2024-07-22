define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config) {
        var $dataForm = $('#' + config.formId);

        var $pageField = $('<input type="hidden">').attr({
            id: 'newsletter_page',
            name: 'page',
            value: window.location.pathname
        });

        $dataForm.append($pageField);

        $dataForm.submit(function () {
            var $this = $(this);
            $this.find(':submit').attr('disabled', 'disabled');
            if (this.isValid === false) {
                $this.find(':submit').prop('disabled', false);
            }
            this.isValid = true;
        });

        $dataForm.bind('invalid-form.validate', function () {
            $(this).find(':submit').prop('disabled', false);
            this.isValid = false;
        });
    };
});
