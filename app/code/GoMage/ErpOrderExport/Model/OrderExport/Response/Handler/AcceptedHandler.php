<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderExport\Response\Handler;

use GoMage\ErpOrderExport\Model\Logger;
use GoMage\ErpOrderExport\Model\Source\Status;
use Magento\Sales\Api\OrderRepositoryInterface;

class AcceptedHandler implements HandlerInterface
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
        $registrationId = $response['details']['order']['@attributes']['registrationId'] ?? null;
        $order->setData('smartblinds_registration_id', $registrationId);
        $order->setData('smartblinds_registration_status', Status::ACCEPTED);
        $order->setData('smartblinds_registration_error', null);

        $responseItems = $response['details']['order']['items'] ?? [];

        foreach ($responseItems as $item) {
            $itemId = $item['@attributes']['id'] ?? null;
            $orderItem = $this->retrieveOrderItem($order, $itemId);
            if (!$orderItem) {
                continue;
            }
            $orderItem->setData(
                'smartblinds_registration_id',
                $item['@attributes']['registrationId'] ?? null
            );
        }

        $this->orderRepository->save($order);

        $this->logger->scheduleMessage("[{$order->getIncrementId()}] export success");
    }

    private function retrieveOrderItem($order, $itemId)
    {
        foreach ($order->getItems() as $orderItem) {
            if ((int) $itemId == (int) $orderItem->getItemId()) {
                return $orderItem;
            }
        }
        return null;
    }
}
