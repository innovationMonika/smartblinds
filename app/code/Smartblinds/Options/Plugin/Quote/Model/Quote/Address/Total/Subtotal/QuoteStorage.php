<?php

namespace Smartblinds\Options\Plugin\Quote\Model\Quote\Address\Total\Subtotal;

class QuoteStorage
{
    private $quote = null;

    public function beforeCollect(
        \Magento\Quote\Model\Quote\Address\Total\Subtotal $subject,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->quote = $quote;
        return null;
    }

    /**
     * @return null|\Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }
}
