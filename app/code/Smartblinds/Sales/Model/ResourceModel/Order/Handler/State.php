<?php

namespace Smartblinds\Sales\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;

class State extends \Magento\Sales\Model\ResourceModel\Order\Handler\State
{
    public function check(Order $order)
    {
        $currentState = $order->getState();
        if ($currentState == Order::STATE_NEW && $order->getIsInProcess()) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $currentState = Order::STATE_PROCESSING;
        }

        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice()) {
            if (in_array($currentState, [Order::STATE_PROCESSING, Order::STATE_COMPLETE])
                && !$order->canCreditmemo()
                && !$order->canShip()
                && $order->getIsNotVirtual()
            ) {
                $state = $order->getData('is_samples_order') ?
                    Order::STATE_CLOSED : Order::STATE_CANCELED;
                $order
                    ->setState($state)
                    ->setStatus($order->getConfig()->getStateDefaultStatus($state));
            } elseif ($currentState === Order::STATE_PROCESSING && !$order->canShip()) {
                $order->setState(Order::STATE_COMPLETE)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
            }
        }
        return $this;
    }
}
