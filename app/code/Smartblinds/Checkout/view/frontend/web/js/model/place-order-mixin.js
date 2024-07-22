define([
    'jquery',
    'mage/utils/wrapper',
], function (
    $,
    wrapper
) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, serviceUrl, payload, messageContainer) {
            const
                $orderTypeElement = $('select[name=order_type]'),
                $baseIncrementIdElement = $('input[name=base_increment_id]');
            const orderType = $orderTypeElement.val();
            const baseIncrementId = $baseIncrementIdElement.val();
            if (!orderType && !baseIncrementId) {
                return originalAction(serviceUrl, payload, messageContainer);
            }
            if (payload.paymentMethod.extensionAttributes === undefined) {
                payload.paymentMethod.extensionAttributes = {};
            }
            if (orderType) {
                payload.paymentMethod.extensionAttributes.orderType = orderType;
            }
            if (baseIncrementId && orderType === 'WARRANTY') {
                payload.paymentMethod.extensionAttributes.baseIncrementId = baseIncrementId;
            }
            return originalAction(serviceUrl, payload, messageContainer);
        });
    };
});
