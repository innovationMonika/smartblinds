<?php

namespace Smartblinds\System\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory as CollectionFactory;

class System implements OptionSourceInterface
{
    private CollectionFactory $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create();
        $options = [];
        foreach ($collection as $item) {
            $options[] = [
                'label' => $item->getName(),
                'value' => $item->getId()
            ];
        }
        return $options;
    }
}
