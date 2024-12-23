<?php

namespace Smartblinds\AutoInvoice\Console;

use Psr\Log\LoggerInterface;
use Smartblinds\AutoInvoice\Model\InvoiceProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class Process extends Command
{
    const COMMAND_NAME = 'smartblinds:autoinvoice:process';
    const COMMAND_DESCRIPTION = 'Create invoices according to configuration.';
	const OPTION_DRY_RUN = 'dry-run';

    private State $state;
    private LoggerInterface $logger;
    private InvoiceProcessor $invoiceProcessor;

    public function __construct(
        State $state,
        LoggerInterface $logger,
        InvoiceProcessor $invoiceProcessor
    ) {
        $this->state = $state;
        $this->logger = $logger;
        $this->invoiceProcessor = $invoiceProcessor;
        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                self::OPTION_DRY_RUN,
                null,
                InputOption::VALUE_OPTIONAL,
                'Simulation mode',
                false
            )
        ];

        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->setDefinition($options);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {

            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode('adminhtml');
        }

        $output->writeln('<fg=green>Starting auto invoice procedure</>');
        $dryRun = $input->getOption(self::OPTION_DRY_RUN);
        if ($dryRun) {
            $output->writeln('<fg=yellow>This is a dry run, no orders will actually be invoiced.</>');
        }

        $orders = $this->invoiceProcessor->getOrdersToProcess();
        foreach ($orders as $order) {
            try {

                $message = sprintf(
    				'Invoicing order #%s',
    				$order->getIncrementId()
    			);
    			$output->writeln('<fg=green>' . $message . '</>');

                if ($dryRun) {
                    continue;
                }

                $this->logger->info($message);
			    $this->invoiceProcessor->invoice($order);

        	} catch (\Exception $ex) {
        		$output->writeln(sprintf(
    				'<fg=red>%s</>',
    				$ex->getMessage()
    			));
    			$this->logger->critical($ex->getMessage());
        	}
        }

    	$output->writeln('<fg=green>Auto invoice procedure completed.</>');
    }
}
