<?php declare(strict_types=1);

namespace Smartblinds\System\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private Json $json;
    private StoreManagerInterface $storeManager;
    private Context $httpContext;

    private $isShowControlType;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json,
        StoreManagerInterface $storeManager,
        Context $httpContext
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
    }

    public function getAlternateMapping($systemCategory): array
    {
        if (!$systemCategory) {
            return [];
        }

        $config = [];
        foreach (['color', 'type', 'size'] as $setting) {
            $data = $this->json->unserialize(
                $this->scopeConfig->getValue("smartblinds_system/$setting/alternate") ?? '[]'
            );
            $data = array_filter($data, function ($row) use ($setting) {
                return $row && $row['system_category'] && $row['system_type']
                    && $row['option'] && $row['option_alternate'];
            });
            $data = array_filter($data, function ($row) use ($systemCategory) {
                return $row['system_category'] == $systemCategory;
            });
            $config = array_merge($config, $data);
        }

        $mapping = [];
        foreach ($config as $row) {
            $mapping[$row['system_type']][$row['option']] = (int) $row['option_alternate'];
        }

        return $mapping;
    }

    public function getSystemPrice($system, $priceType)
    {
        $data = $this->json->unserialize(
            $this->scopeConfig->getValue("smartblinds_system/$priceType/alternate") ?? '[]'
        );
        foreach ($data as $row) {
            $storeId = $row['store'] ?? null;
            $systemId = $row['system'] ?? null;
            $price = $row['price'] ?? null;
            if ($price && $storeId == $this->storeManager->getStore()->getId() && $systemId == $system->getId()) {
                return $row['price'];
            }
        }
        return null;
    }

    public function getSystemDimensionValue($system, $type)
    {
        $data = $this->json->unserialize(
            $this->scopeConfig->getValue("smartblinds_system/$type/alternate") ?? '[]'
        );
        foreach ($data as $row) {
            $storeId = $row['store'] ?? null;
            $systemId = $row['system'] ?? null;
            $price = $row['value'] ?? null;
            if ($price && $storeId == $this->storeManager->getStore()->getId() && $systemId == $system->getId()) {
                return $row['value'];
            }
        }
        return null;
    }

    public function getShowControlTypeCustomerGroups(): array
    {
        return explode(',', $this->scopeConfig->getValue('smartblinds_system/type/customer_group') ?? '');
    }

    public function isShowControlType()
    {
        if (isset($this->isShowControlType)) {
            return $this->isShowControlType;
        }

        $customerGroup = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
        $groups = $this->getShowControlTypeCustomerGroups();

        $this->isShowControlType = (string) ((int) in_array($customerGroup, $groups));
        return $this->isShowControlType;
    }
}
