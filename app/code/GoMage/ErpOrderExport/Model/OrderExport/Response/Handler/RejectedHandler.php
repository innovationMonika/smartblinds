<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderExport\Response\Handler;

use GoMage\ErpOrderExport\Model\Logger;
use GoMage\ErpOrderExport\Model\Source\Status;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class RejectedHandler implements HandlerInterface
{
    private OrderRepositoryInterface $orderRepository;
    private Logger $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Logger $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function handle($order, array $response)
    {
        $rejectionReason = $response['rejectionReason'] ?? 'No rejection reason';
        $order->setData('smartblinds_registration_error', $rejectionReason);
        $order->setData('smartblinds_registration_status', Status::REJECTED);

        $this->orderRepository->save($order);

        $this->logger->scheduleMessage("[{$order->getIncrementId()}] export reject");
    }
}
