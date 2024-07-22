<?php declare(strict_types=1);

namespace GoMage\Samples\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getOrderStatus(): string
    {
        return (string) $this->scopeConfig->getValue('gomage_samples/order/status');
    }

    public function getProductImageAttribute(): string
    {
        return (string) $this->scopeConfig->getValue('gomage_samples/sample/product_image_attribute');
    }
}
