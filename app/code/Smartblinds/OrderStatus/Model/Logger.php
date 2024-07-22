<?php

namespace Smartblinds\OrderStatus\Model;

use Magento\Framework\Debug;
use Psr\Log\LoggerInterface;

class Logger
{
    private LoggerInterface $logger;
    private Config $config;

    public function __construct(
        LoggerInterface $logger,
        Config $config
    ) {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function info($message, array $context = [])
    {
        if ($this->config->isLoggingEnabled()) {
            $this->logger->info($message, $context);
            if ($this->config->isLogBacktrace()) {
                $this->logger->info(PHP_EOL . Debug::backtrace(true, false));
            }
        }
    }
}
