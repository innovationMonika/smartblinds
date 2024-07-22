<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params;

use GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\Collector\CollectorInterface;

class CollectorPool implements CollectorPoolInterface
{
    /** @var CollectorInterface[]  */
    private array $collectors;

    public function __construct(array $collectors = [])
    {
        $this->collectors = $collectors;
    }

    public function get(string $code): ?CollectorInterface
    {
        return $this->collectors[$code] ?? null;
    }
}
