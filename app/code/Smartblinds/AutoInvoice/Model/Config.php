<?php

namespace Smartblinds\AutoInvoice\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isCronEnabled()
    {
        return $this->scopeConfig->isSetFlag('smartblinds_autoinvoice/cron/active');
    }
}
