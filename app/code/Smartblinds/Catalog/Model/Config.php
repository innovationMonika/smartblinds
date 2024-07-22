<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private Json $json;

    private $relativeConfig;
    private $colorGroupConfig;
    private $addToCartCategoryUrlKeys;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
    }

    public function getRelativeConfig(): array
    {
        if (!isset($this->relativeConfig)) {
            $this->relativeConfig = $this->getConfig('relative_data/config');
        }
        return $this->relativeConfig;
    }

    public function getColorGroupConfig(): array
    {
        if (!isset($this->colorGroupConfig)) {
            $this->colorGroupConfig = $this->getConfig('color_group/config');
        }
        return $this->colorGroupConfig;
    }

    public function getAddToCartCategoryUrlKeys(): array
    {
        if (!isset($this->addToCartCategoryUrlKeys)) {
            $keys = $this->scopeConfig->getValue('smartblinds_catalog/texts/add_to_cart_category_url_keys');
            $keys = is_string($keys) ? $keys : '';
            $keys = array_values(
                array_filter(
                    array_map(
                        'trim',
                        explode(PHP_EOL, $keys)
                    )
                )
            );
            $this->addToCartCategoryUrlKeys = $keys;
        }
        return $this->addToCartCategoryUrlKeys;
    }

    private function getConfig(string $path): array
    {
        $this->scopeConfig->clean();
        $json = $this->scopeConfig->getValue("smartblinds_catalog/$path");
        try {
            $config = $this->json->unserialize($json);
            if (!is_array($config)) {
                throw new \InvalidArgumentException();
            }
        } catch (\InvalidArgumentException $e) {
            return [];
        }
        return array_values($config);
    }
}
