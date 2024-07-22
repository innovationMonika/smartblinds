define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data'
], function ($, getTotalsAction, customerData) {
    $(document).ready(function(){
        $(document).on(
            'input',
            'input[name$="[qty]"]',
            function(){
                reloadCart();
            });
        $(document).on(
            'click',
            '.qty-btn',
            function(){
                let val_input = $(this).closest('div.qty').find('.input-text.qty').val();
                let number_click;
                if ($(this).hasClass('decrease-qty')) {
                    number_click = parseInt(val_input) - 1;
                } else if ($(this).hasClass('increase-qty')) {
                    number_click = parseInt(val_input) + 1;
                }

                val_input = parseInt(val_input);

                if(number_click && val_input <= number_click){
                    val_input = number_click;
                } else {
                    val_input = val_input - 1;
                }

                if (val_input > 0) {
                    $(this).closest('div.qty').find('.input-text.qty').val(val_input);
                    reloadCart();
                }
                return false;
            });
    });
    function reloadCart(){
        let form = $('form#form-validate');
        $.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            showLoader: true,
            success: function (res) {
                let parsedResponse = $.parseHTML(res);
                let result = $(parsedResponse).find("#form-validate");
                let sections = ['cart'];

                $("#form-validate").replaceWith(result);

                /* Minicart reloading */
                customerData.reload(sections, true);

                /* Totals summary reloading */
                let deferred = $.Deferred();
                getTotalsAction([], deferred);
            },
            error: function (xhr, status, error) {
                let err = eval("(" + xhr.responseText + ")");
                console.log(err.Message);
            }
        });
    }
});
