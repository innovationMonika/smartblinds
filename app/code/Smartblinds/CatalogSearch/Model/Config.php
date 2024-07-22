<?php declare(strict_types=1);

namespace Smartblinds\CatalogSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private Json $json;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
    }

    public function getProductTypesToExcludeFromLayer(): array
    {
        return explode(
            ',',
            $this->scopeConfig->getValue('smartblinds_catalog_search/general/product_types_to_exclude_from_layer')
        );
    }
}
