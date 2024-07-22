<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderData;

use Magento\Sales\Api\Data\OrderInterface;

class Composite implements DataProviderInterface
{
    /** @var DataProviderInterface[] */
    private array $dataProviders;

    public function __construct(array $dataProviders = [])
    {
        $this->dataProviders = $dataProviders;
    }

    public function getData(OrderInterface $order): array
    {
        $data = [];
        foreach ($this->dataProviders as $dataProvider) {
            $data = array_merge_recursive($data, $dataProvider->getData($order));
        }
        return $data;
    }
}
