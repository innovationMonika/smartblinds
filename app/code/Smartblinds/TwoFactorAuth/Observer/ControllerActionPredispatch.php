<?php

declare(strict_types=1);

namespace Smartblinds\TwoFactorAuth\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Smartblinds\TwoFactorAuth\Model\HandleActionPredispatch;

class ControllerActionPredispatch implements ObserverInterface
{
    private Manager $moduleManager;

    public function __construct(
        Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    public function execute(Observer $observer)
    {
        if ($this->moduleManager->isEnabled('Magento_TwoFactorAuth')) {
            ObjectManager::getInstance()
                ->get(HandleActionPredispatch::class)
                ->execute($observer);
        }
    }
}
