<?php declare(strict_types=1);

namespace GoMage\BaseSuggestions\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class Config
{
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;
    private Json $json;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->storeManager = $storeManager;
    }

    public function getBaseSuggestions(): array
    {
        try {
            $json = $this->scopeConfig->getValue('gomage_base_suggestions/general/suggestions', ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
            if ($json) {
                return $this->json->unserialize($json);
            }
        } catch (\InvalidArgumentException $e) {}
        return [];
    }
}
