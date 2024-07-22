<?php

namespace Smartblinds\OrderStatus\Plugin\Sales\Model\Order\Log;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Smartblinds\OrderStatus\Model\Logger;

class Set
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function afterSetData(
        Order $subject,
        $result,
        $key,
        $value = null
    ) {
        if ($key !== OrderInterface::STATUS) {
            return $result;
        }
        $messageParts = [
            'ORDER STATUS SET',
            'ORDER ID ' . ($subject->getId() ?: 'new'),
            'STATUS VALUE ' . $value
        ];
        $this->logger->info(implode(' | ', $messageParts));
        return $result;
    }
}
