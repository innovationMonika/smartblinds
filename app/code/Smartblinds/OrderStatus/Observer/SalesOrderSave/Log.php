<?php

namespace Smartblinds\OrderStatus\Observer\SalesOrderSave;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Smartblinds\OrderStatus\Model\Logger;

class Log implements ObserverInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $messageParts = [
            'ORDER STATUS SAVE',
            'ORDER ID ' . ($order->getId() ?: 'new'),
            'STATUS VALUE ' . $order->getStatus()
        ];
        $this->logger->info(implode(' | ', $messageParts));
    }
}
