<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderExport\Response\Handler;

class NullHandler implements HandlerInterface
{
    public function handle($order, array $response)
    {
    }
}
