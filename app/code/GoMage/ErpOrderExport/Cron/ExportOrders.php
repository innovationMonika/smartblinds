<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Cron;

use GoMage\ErpOrderExport\Model\Config;
use GoMage\ErpOrderExport\Model\OrderExport;
use GoMage\ErpOrderExport\Model\OrderProvider;

class ExportOrders
{
    private OrderProvider $orderProvider;
    private OrderExport $orderExport;
    private Config $config;

    public function __construct(
        OrderProvider $orderProvider,
        OrderExport $orderExport,
        Config $config
    ) {
        $this->orderProvider = $orderProvider;
        $this->orderExport = $orderExport;
        $this->config = $config;
    }

    public function execute()
    {
        if (!$this->config->isExportEnabled()) {
            return;
        }

        foreach ($this->orderProvider->loadOrders() as $order) {
            $this->orderExport->sendOrder($order);
        }
    }
}
