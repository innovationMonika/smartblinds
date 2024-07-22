<?php declare(strict_types=1);

namespace GoMage\Samples\Plugin\InventorySales\Model\PlaceReservationsForSalesEvent;

use GoMage\Samples\Model\Claim\PlaceOrder\SamplesChecker;
use GoMage\Samples\Model\ResourceModel\OrderChecker;
use Magento\InventorySales\Model\PlaceReservationsForSalesEvent;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

class PreventReservationsForSamples
{
    private OrderChecker $orderChecker;
    private SamplesChecker $samplesChecker;

    public function __construct(
        OrderChecker $orderChecker,
        SamplesChecker $samplesChecker
    ) {
        $this->orderChecker = $orderChecker;
        $this->samplesChecker = $samplesChecker;
    }

    public function aroundExecute(
        PlaceReservationsForSalesEvent $subject,
        callable $proceed,
        array $items,
        SalesChannelInterface $salesChannel,
        SalesEventInterface $salesEvent
    ) {
        $isOrderType = $salesEvent->getObjectType() === SalesEventInterface::OBJECT_TYPE_ORDER;
        if (!$isOrderType) {
            return $proceed($items, $salesChannel, $salesEvent);
        }

        $attributes = $salesEvent->getExtensionAttributes();
        $id = $attributes ? ($attributes->__toArray()['objectIncrementId'] ?? null) : null;
        if (
            $id && ($this->samplesChecker->isSamplesOrder($id) || $this->orderChecker->isSamplesOrderByIncrementId($id))
        ) {
            return;
        }

        $objectId = $salesEvent->getObjectId();
        if ($objectId && $this->orderChecker->isSamplesOrderById($objectId)) {
            return;
        }

        return $proceed($items, $salesChannel, $salesEvent);
    }
}
