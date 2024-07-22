<?php

namespace Smartblinds\PreloadLinks\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isRenderEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('smartblinds_preload_links/general/render_enabled');
    }

    public function isCollectForCmsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('smartblinds_preload_links/cms/collect_enabled');
    }

    public function isCollectForCategoryEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('smartblinds_preload_links/category/collect_enabled');
    }

    public function getCategoryImageDisplayAreas(): array
    {
        return $this->getArrayValues('smartblinds_preload_links/category/image_display_areas');
    }

    public function isCollectForProductEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('smartblinds_preload_links/product/collect_enabled');
    }

    public function getProductMainImageKey(): string
    {
        return (string) $this->scopeConfig->getValue('smartblinds_preload_links/product/main_image_key');
    }

    private function getArrayValues(string $path): array
    {
        $value = $this->scopeConfig->getValue($path);
        $value = is_string($value) ? $value : '';
        return array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(PHP_EOL, $value)
                )
            )
        );
    }
}
