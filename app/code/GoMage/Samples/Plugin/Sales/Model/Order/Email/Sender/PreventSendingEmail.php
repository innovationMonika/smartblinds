<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\Sales\Model\Order\Email\Sender;

use GoMage\Samples\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class PreventSendingEmail
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function aroundSend(
        OrderSender $subject,
        callable $proceed,
        Order $order,
        $forceSyncMode = false
    ) {
        if ($order->getStatus() === $this->config->getOrderStatus()) {
            return false;
        }

        return $proceed($order, $forceSyncMode);
    }
}
