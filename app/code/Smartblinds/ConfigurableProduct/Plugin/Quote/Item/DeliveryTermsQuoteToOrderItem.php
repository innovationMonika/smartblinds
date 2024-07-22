<?php
namespace Smartblinds\ConfigurableProduct\Plugin\Quote\Item;

class DeliveryTermsQuoteToOrderItem
{
    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param $additional
     * @return \Magento\Sales\Model\Order\Item
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setDeliveryTerms($item->getDeliveryTerms());
        return $orderItem;
    }
}
