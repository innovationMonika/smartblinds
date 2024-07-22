<?php

declare(strict_types = 1);

namespace GoMage\ErpOrderExport\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderStatus
{
    private Transport $transport;
    private OrderRepositoryInterface $orderRepository;
    private Logger $logger;

    public function __construct(
        Transport $transport,
        OrderRepositoryInterface $orderRepository,
        Logger $logger
    ) {
        $this->transport = $transport;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function update($orders)
    {
        $incrementIds = $this->getIncrementIds($orders);
        $response = $this->transport->get($incrementIds);

        $responseOrders = $response['orders'] ?? [];
        foreach ($responseOrders as $responseOrder) {
            $this->processOrder($orders, $responseOrder);
        }
    }

    private function processOrder($orders, array $responseOrder)
    {
        $incrementId = $responseOrder['@attributes']['id'] ?? null;
        $order = $this->findOrder($orders, $incrementId);
        if (!$order) {
            return;
        }

        $this->logger->resetMessages();
        $this->logger->scheduleMessage("[$incrementId] status start");

        $status = $responseOrder['@attributes']['status'] ?? null;
        if ($status !== 'COMPLETED') {
            $this->logger->scheduleMessage("[$incrementId] status uncompleted");
        }

        try {
            $order->setState(Order::STATE_COMPLETE);
            $order->setStatus(Order::STATE_COMPLETE);
            $this->orderRepository->save($order);
            $this->logger->scheduleMessage("[{$order->getIncrementId()}] status completed");
        } catch (\Exception $e) {
            $this->logger->scheduleMessage($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->logger->scheduleMessage("[{$order->getIncrementId()}] status error");
        } finally {
            $this->logger->writeMessages();
        }
    }

    private function getIncrementIds($orders)
    {
        return array_values(array_map(function ($order) {
            return $order->getIncrementId();
        }, $orders));
    }

    private function findOrder($orders, $incrementId): ?OrderInterface
    {
        $order = array_filter($orders, function ($order) use ($incrementId) {
            return $order->getIncrementId() == $incrementId;
        });
        $order = reset($order);
        return $order ?: null;
    }
}
