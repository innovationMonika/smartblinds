<?php declare(strict_types=1);

namespace GoMage\Samples\Observer\QuoteSubmit;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SetDataToOrder implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $orderAddress = $order->getShippingAddress();
        $quoteAddress = $quote->getShippingAddress();

        foreach ($this->getFields() as $field) {
            $value = $quoteAddress->getData($field);
            $orderAddress->setData($field, $value);
        }
    }

    private function getFields(): array
    {
        return ['gender', 'samples_form_id'];
    }
}
