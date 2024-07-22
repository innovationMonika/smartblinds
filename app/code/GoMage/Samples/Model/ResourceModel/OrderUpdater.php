<?php

namespace GoMage\Samples\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Order;

class OrderUpdater
{
    private Order $orderResource;

    public function __construct(Order $orderResource)
    {
        $this->orderResource = $orderResource;
    }

    public function update($orderId)
    {
        $this->orderResource->getConnection()
            ->update(
                $this->orderResource->getMainTable(),
                $this->getData(),
                ['entity_id = ?' => $orderId]
            );
    }

    private function getData(): array
    {
        return [
            'is_samples_order' => 1
        ];
    }
}
