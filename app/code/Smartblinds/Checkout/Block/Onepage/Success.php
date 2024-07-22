<?php

namespace Smartblinds\Checkout\Block\Onepage;

/**
 * One page checkout success page for Google Analytics
 *
 * @api
 * @since 100.0.2
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        $this->addData(
            [
                'is_order_visible' => $this->isVisible($order),
                'view_order_url' => $this->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getEntityId()]
                ),
                'print_url' => $this->getUrl(
                    'sales/order/print',
                    ['order_id' => $order->getEntityId()]
                ),
                'can_print_order' => $this->isVisible($order),
                'can_view_order'  => $this->canViewOrder($order),
                'order_id'  => $order->getIncrementId(),
                'customer_email' => $order->getCustomerEmail(),
            ]
        );
    }
}
