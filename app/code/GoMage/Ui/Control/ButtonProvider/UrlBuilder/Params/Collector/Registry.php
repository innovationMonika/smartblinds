<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\Collector;

use GoMage\Ui\Model\EntityRegistry;

class Registry implements CollectorInterface
{
    private EntityRegistry $registry;

    public function __construct(EntityRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function collect(array $params): array
    {
        $data = [];
        foreach ($params as $paramData) {
            $entity = $this->registry->get($paramData['registryKey']);
            if (!$entity) {
                continue;
            }
            $dataKey = $paramData['dataKey'];
            if ($value = $entity->getData($dataKey)) {
                $requestKey = $paramData['requestKey'];
                $data[$requestKey] = $value;
            }
        }
        return $data;
    }
}
