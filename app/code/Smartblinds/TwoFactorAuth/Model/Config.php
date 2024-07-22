<?php

namespace Smartblinds\TwoFactorAuth\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getUsersWithDisabled2Fa()
    {
        return explode(',', $this->scopeConfig->getValue('smartblinds_twofactorauth/general/users_with_disabled_2fa') ?: '');
    }
}
