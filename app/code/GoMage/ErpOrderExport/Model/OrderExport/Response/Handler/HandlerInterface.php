<?php

namespace GoMage\ErpOrderExport\Model\OrderExport\Response\Handler;

interface HandlerInterface
{
    public function handle($order, array $response);
}
