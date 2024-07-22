<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Console\Command;

use GoMage\ErpOrderExport\Model\Config;
use GoMage\ErpOrderExport\Model\OrderExport;
use GoMage\ErpOrderExport\Model\OrderProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportOrders extends Command
{
    private OrderProvider $orderProvider;
    private OrderExport $orderExport;
    private Config $config;
    private State $appState;

    public function __construct(
        OrderProvider $orderProvider,
        OrderExport $orderExport,
        Config $config,
        State $appState,
        string $name = null
    ) {
        $this->orderProvider = $orderProvider;
        $this->orderExport = $orderExport;
        $this->config = $config;
        parent::__construct($name);
        $this->appState = $appState;
    }

    protected function configure()
    {
        $this->setName('erp:order:export');
        $this->setDescription('Export Orders');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch(\Exception $e){}

        if (!$this->config->isExportEnabled()) {
            return;
        }

        foreach ($this->orderProvider->loadOrders() as $order) {
            $this->orderExport->sendOrder($order);
        }
    }
}
