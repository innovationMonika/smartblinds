<?php

namespace Smartblinds\Options\Observer\Quote\Item\Option;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item\Option;

class EncodeArrayFields implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $object = $observer->getObject();
        if ($object instanceof Option && is_array($object->getValue())) {
            $object->setValue(json_encode($object->getValue()));
        }
    }
}
