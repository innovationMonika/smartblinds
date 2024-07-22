<?php declare(strict_types=1);

namespace GoMage\Samples\ViewModel;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Options;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Claim implements ArgumentInterface
{
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;
    private Data $directoryHelper;
    private UrlInterface $url;
    private Country $countrySource;
    private Json $json;
    private Options $customerOptions;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Data $directoryHelper,
        UrlInterface $url,
        Country $countrySource,
        Json $json,
        Options $customerOptions
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        $this->url = $url;
        $this->countrySource = $countrySource;
        $this->json = $json;
        $this->customerOptions = $customerOptions;
    }

    public function getMinimumPasswordLength()
    {
        return $this->scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    public function getRequiredCharacterClassesNumber()
    {
        return $this->scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }

    public function getCurrentCountryCode(): string
    {
        return $this->directoryHelper->getDefaultCountry($this->storeManager->getStore());
    }

    public function getPlaceOrderUrl(): string
    {
        return $this->url->getDirectUrl('/rest/V1/samples/claim/place-order');
    }

    public function getCountriesJson(): string
    {
        return $this->json->serialize(array_values(array_filter($this->countrySource->toOptionArray(), function ($option) {
            return in_array($option['value'], $this->directoryHelper->getTopCountryCodes());
        })));
    }

    public function getPrivacyUrl(): string
    {
        return $this->url->getDirectUrl('privacy');
    }

    public function getTermsConditionsUrl(): string
    {
        return $this->url->getDirectUrl('algemene-voorwaarden');
    }

    public function getPrefixOptions(): array
    {
        return array_map(function ($option) {
            return (string) $option;
        }, $this->customerOptions->getNamePrefixOptions($this->storeManager->getStore()->getId()));
    }

    public function getDefaultPrefix(): string
    {
        $prefixOptions = $this->getPrefixOptions();
        $prefixOption = reset($prefixOptions);
        return $prefixOption ? (string) $prefixOption : '';
    }
}
