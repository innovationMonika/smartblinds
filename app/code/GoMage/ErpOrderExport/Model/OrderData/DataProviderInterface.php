<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderData;

use Magento\Sales\Api\Data\OrderInterface;

interface DataProviderInterface
{
    public function getData(OrderInterface $order): array;
}
