<?php
namespace Smartblinds\ConfigurableProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetQuoteItemDeliveryTerms implements ObserverInterface
{
    const ATTRIBUTE_CODE = 'delivery_terms';

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if ($quoteItem->getProductType() === 'simple') {
            $product = $observer->getProduct();
            $value = $product->getData(self::ATTRIBUTE_CODE);
            $parentItem = $quoteItem->getParentItem();
            if ($parentItem && $parentItem->getProductType() === 'configurable') {
                $parentItem->setDeliveryTerms($value);
            }
            $quoteItem->setDeliveryTerms($value);
        }
    }
}
