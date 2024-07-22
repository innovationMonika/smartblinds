<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isExportEnabled(): bool
    {
        return (bool) $this->getValue('enabled');
    }

    public function getApiUrl(): string
    {
        return (string) $this->getValue('api_url');
    }

    public function getApiQueryUrl(): string
    {
        return (string) $this->getValue('api_query_url');
    }

    public function isReverseStreet($storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            'erp/order_export/reverse_street',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    private function getValue(string $field)
    {
        return $this->scopeConfig->getValue("erp/order_export/{$field}");
    }
}
