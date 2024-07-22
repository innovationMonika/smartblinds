<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params;

use GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\Collector\CollectorInterface;

interface CollectorPoolInterface
{
    public function get(string $code): ?CollectorInterface;
}
