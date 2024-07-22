<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Cron;

use GoMage\ErpOrderExport\Model\Config;
use GoMage\ErpOrderExport\Model\OrderProvider;
use GoMage\ErpOrderExport\Model\OrderStatus;

class StatusOrders
{
    protected OrderStatus $orderStatus;
    protected OrderProvider $orderProvider;
    private Config $config;

    public function __construct(
        OrderProvider $orderProvider,
        Config $config,
        OrderStatus $orderStatus
    ) {
        $this->orderStatus = $orderStatus;
        $this->orderProvider = $orderProvider;
        $this->config = $config;
    }

    public function execute()
    {
        if (!$this->config->isExportEnabled()) {
            return;
        }

        $this->orderStatus->update($this->orderProvider->loadSentOrders());
    }
}
