<?php declare(strict_types=1);

namespace Smartblinds\Newsletter\Observer\Subscriber\Save;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Smartblinds\Newsletter\Model\AdditionalData;

class AddAdditionalData implements ObserverInterface
{
    private AdditionalData $additionalData;

    public function __construct(AdditionalData $additionalData)
    {
        $this->additionalData = $additionalData;
    }

    public function execute(Observer $observer)
    {
        /** @var Subscriber $subscriber */
        $subscriber = $observer->getEvent()->getData('data_object');
        $subscriber->addData($this->additionalData->getData());
    }
}
