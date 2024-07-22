<?php declare(strict_types=1);

namespace GoMage\Samples\Observer\QuoteSubmit;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SetSamplesOrder implements ObserverInterface
{
    private SamplesChecker $samplesChecker;

    public function __construct(SamplesChecker $samplesChecker)
    {
        $this->samplesChecker = $samplesChecker;
    }

    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');

        if ($this->samplesChecker->isSamplesQuote($quote->getId())) {
            $this->samplesChecker->setSamplesOrder($order->getIncrementId());
        }
    }
}
