<?php declare(strict_types=1);

namespace GoMage\PaidCustomerGroup\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getValue('enabled');
    }

    public function getGroupId(): string
    {
        return (string) $this->getValue('id');
    }

    public function getAmount(): int
    {
        return (int) $this->getValue('amount');
    }

    private function getValue(string $field)
    {
        return $this->scopeConfig->getValue("gomage_paid_customer_group/general/{$field}");
    }
}
