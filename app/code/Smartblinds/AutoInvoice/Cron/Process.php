<?php

namespace Smartblinds\AutoInvoice\Cron;

use Psr\Log\LoggerInterface;
use Smartblinds\AutoInvoice\Model\Config;
use Smartblinds\AutoInvoice\Model\InvoiceProcessor;

class Process
{
    private Config $config;
    private LoggerInterface $logger;
    private InvoiceProcessor $invoiceProcessor;

    public function __construct(
        Config $config,
        LoggerInterface $logger,
    	InvoiceProcessor $invoiceProcessor
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->invoiceProcessor = $invoiceProcessor;
    }

    public function execute()
    {
        if (!$this->config->isCronEnabled()) {
            return;
        }

        $this->logger->info('Starting auto invoice procedure.');

        foreach ($this->invoiceProcessor->getOrdersToProcess() as $order) {
            try {
                $this->logger->info(sprintf(
    				'Invoicing order #%s',
    				$order->getIncrementId()
    			));
    			$this->invoiceProcessor->invoice($order);
        	} catch (\Exception $ex) {
        		$this->logger->critical($ex->getMessage());
        	}
        }

        $this->logger->info('Auto invoice procedure completed.');
    }
}
