<?php declare(strict_types=1);

namespace Smartblinds\Theme\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getHeadContent(): string
    {
        return $this->scopeConfig
            ->getValue(
                'smartblinds_theme/general/head',
                ScopeInterface::SCOPE_STORE
            ) ?: '';
    }

    public function isCssCriticalEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
        'dev/css/use_css_critical_path',
            ScopeInterface::SCOPE_STORE
        );
    }
}
