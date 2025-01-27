<?php

declare(strict_types = 1);

namespace GoMage\ErpOrderExport\Model;

use GoMage\ErpOrderExport\Model\OrderData\DataProviderInterface;
use GoMage\ErpOrderExport\Model\OrderExport\Response\Handler\HandlerPool;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderExport
{
    private DataProviderInterface $dataProvider;
    private Transport $transport;
    private OrderRepositoryInterface $orderRepository;
    private Logger $logger;
    private HandlerPool $handlerPool;

    public function __construct(
        DataProviderInterface $dataProvider,
        Transport $transport,
        OrderRepositoryInterface $orderRepository,
        Logger $logger,
        HandlerPool $handlerPool
    ) {
        $this->dataProvider = $dataProvider;
        $this->transport = $transport;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->handlerPool = $handlerPool;
    }

    public function sendOrder(OrderInterface $order)
    {
        try {
            $this->logger->resetMessages();
            $this->logger->scheduleMessage("[{$order->getIncrementId()}] export start");

            $data = $this->dataProvider->getData($order);

            $response = $this->transport->send($data);
            $status = $response['@attributes']['status'] ?? 'rejected';
            $this->handlerPool->getByStatus($status)->handle($order, $response);

        } catch (\Exception $e) {
            $this->logger->scheduleMessage($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->logger->scheduleMessage("[{$order->getIncrementId()}] export error");
        } finally {
            $this->saveOrder($order);
            $this->logger->writeMessages();
        }
    }

    private function saveOrder(OrderInterface $order)
    {
        try {
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->logger->scheduleMessage($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->logger->scheduleMessage("[{$order->getIncrementId()}] save error");
        }
    }
}
