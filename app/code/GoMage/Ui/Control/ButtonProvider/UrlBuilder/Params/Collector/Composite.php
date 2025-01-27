<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\Collector;

use GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\CollectorPoolInterface;

class Composite implements CollectorInterface
{
    private CollectorPoolInterface $collectorPool;

    public function __construct(CollectorPoolInterface $collectorPool)
    {
        $this->collectorPool = $collectorPool;
    }

    public function collect(array $params): array
    {
        $result = [];
        foreach ($params as $collectorCode => $collectorParams) {
            if ($collector = $this->collectorPool->get($collectorCode)) {
                $result = array_merge_recursive($result, $collector->collect($collectorParams));
            }
        }
        return $result;
    }
}
