<?php

namespace Smartblinds\TwoFactorAuth\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class User implements OptionSourceInterface
{
    private CollectionFactory $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $result = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection as $user) {
            $result[] = [
                'label' => $user->getUserName(),
                'value' => $user->getId()
            ];
        }
        return $result;
    }
}
