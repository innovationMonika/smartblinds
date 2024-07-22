<?php

declare(strict_types=1);

namespace GoMage\Cron\Console\Command;

use Magento\Cron\Model\Config\Data;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunJob extends Command
{
    private Data $config;
    private State $appState;

    public function __construct(
        Data $config,
        State $appState
    ) {
        parent::__construct();
        $this->appState = $appState;
        $this->config = $config;
    }

    protected function configure()
    {
        $this
            ->setName('cron:run-job')
            ->setDefinition(
                [new InputArgument('job_code', InputArgument::REQUIRED)]
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_CRONTAB);

        foreach ($this->config->getJobs() as $group) {
            foreach ($group as $job) {
                if ($input->getArgument('job_code') != $job['name']) {
                    continue;
                }
                $method = $job['method'] ?? 'execute';
                $job = ObjectManager::getInstance()->get($job['instance']);
                $job->$method();
            }
        }
    }
}
