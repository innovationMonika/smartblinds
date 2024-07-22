<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\Collector;

interface CollectorInterface
{
    public function collect(array $params): array;
}
