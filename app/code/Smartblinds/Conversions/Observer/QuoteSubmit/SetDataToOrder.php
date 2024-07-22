<?php declare(strict_types=1);

namespace Smartblinds\Conversions\Observer\QuoteSubmit;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetDataToOrder implements ObserverInterface
{
    /**
     * Set data to order
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        foreach ($this->getFields() as $field) {
            if ($quote->hasData($field)) {
                $order->setData($field, $quote->getData($field));
            }
        }
    }

    /**
     * Conversion fields
     *
     * @return array
     */
    private function getFields(): array
    {
        return ['gclid', 'fbp', 'fbc'];
    }
}
