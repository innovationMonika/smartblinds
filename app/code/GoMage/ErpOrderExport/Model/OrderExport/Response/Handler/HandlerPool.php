<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderExport\Response\Handler;

class HandlerPool
{
    private NullHandler $nullHandler;

    /** @var HandlerInterface[] */
    private array $handlers;

    public function __construct(
        NullHandler $nullHandler,
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    public function getByStatus(string $status): HandlerInterface
    {
        return $this->handlers[$status] ?? $this->nullHandler;
    }
}
