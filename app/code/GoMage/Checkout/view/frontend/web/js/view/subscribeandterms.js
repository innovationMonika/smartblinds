define(
    [
        'ko',
        'uiComponent'
    ],
    function (ko, Component) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'GoMage_Checkout/form/element/subscribeandterms'
            },
            isRegisterNewsletter: false
        });
    }
);
