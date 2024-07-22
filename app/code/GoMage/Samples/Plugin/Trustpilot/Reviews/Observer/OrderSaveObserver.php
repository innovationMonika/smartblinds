<?php
namespace GoMage\Samples\Plugin\Trustpilot\Reviews\Observer;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use Magento\Framework\Event\Observer as EventObserver;
use GoMage\Samples\Model\ResourceModel\OrderChecker;


class OrderSaveObserver
{
    /**
     * @var OrderChecker
     */
    private OrderChecker $orderChecker;

    /**
     * @var SamplesChecker
     */
    private SamplesChecker $samplesChecker;

    public function __construct(
        OrderChecker $orderChecker,
        SamplesChecker $samplesChecker
    )
    {
        $this->orderChecker = $orderChecker;
        $this->samplesChecker = $samplesChecker;
    }

    /**
     * @param \Trustpilot\Reviews\Observer\OrderSaveObserver $subject
     * @param callable $proceed
     * @param EventObserver $observer
     * @return void
     */
    public function aroundExecute(
        \Trustpilot\Reviews\Observer\OrderSaveObserver $subject,
        callable $proceed,
        EventObserver $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $event->getOrder();
        if ($this->samplesChecker->isSamplesOrder($order->getIncrementId()) || $this->orderChecker->isSamplesOrderByIncrementId($order->getIncrementId())) {
            return;
        }
        return $proceed($observer);
    }
}
