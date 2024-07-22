<?php declare(strict_types=1);

namespace GoMage\Samples\Observer\OrderPaymentPlace;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use GoMage\Samples\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class ChangeOrderStatus implements ObserverInterface
{
    private Config $config;
    private SamplesChecker $samplesChecker;

    public function __construct(
        SamplesChecker $samplesChecker,
        Config $config
    ) {
        $this->samplesChecker = $samplesChecker;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getData('payment');
        if ($this->samplesChecker->isSamplesQuote($payment->getOrder()->getQuoteId())) {
            $payment->getOrder()->setStatus($this->config->getOrderStatus());
            $payment->getOrder()->setState(Order::STATE_COMPLETE);
        }
    }
}
