<?php

namespace Smartblinds\Shipping\Helper;

class ShipmentEmail extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param $orderStatus
     * @return bool
     */
    public function isCheckedEmailCopyOfShipment($orderStatus = null)
    {
        $allowedStatuses = explode(',', $this->scopeConfig->getValue('shipping/shipment_options/checked_email_copy_of_shipment_for_orders_with_statuses') ?? '');
        return in_array($orderStatus, $allowedStatuses);
    }
}
