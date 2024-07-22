<?php

namespace Smartblinds\Tax\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isHideTax()
    {
        return $this->scopeConfig->isSetFlag(
            'smartblinds_tax/cart_display/hide_tax',
            ScopeInterface::SCOPE_STORE
        );
    }
}
