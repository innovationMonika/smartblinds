<?php declare(strict_types=1);

namespace GoMage\Samples\Observer\QuoteSubmit;

use GoMage\Samples\Model\Config;
use GoMage\Samples\Model\Order\Email\SamplesOrderSender;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class SendSamplesEmail implements ObserverInterface
{
    private LoggerInterface $logger;
    private SamplesOrderSender $orderSender;
    private Config $config;

    public function __construct(
        LoggerInterface $logger,
        SamplesOrderSender $orderSender,
        Config $config
    ) {
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getStatus() !== $this->config->getOrderStatus()) {
            return;
        }

        try {
            $this->orderSender->send($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
