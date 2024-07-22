<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model;

use Psr\Log\LoggerInterface;

class Logger
{
    private LoggerInterface $logger;

    /**  @var string[] */
    private array $messages;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function resetMessages()
    {
        $this->messages = [];
    }

    public function scheduleMessage(string $message)
    {
        $this->messages[] = $message;
    }

    public function writeMessages()
    {
        $message = implode(PHP_EOL, $this->messages);
        $this->logger->info($message);
        $this->messages = [];
    }
}
