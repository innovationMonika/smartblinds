<?php

namespace Smartblinds\Checkout\Plugin\Customer\Model\Plugin;

use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Smartblinds\Checkout\Model\Config;

class AllowedCountries
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param Config $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->config = $config;
    }

    /**
     * Retrieve all allowed countries or specific customers
     *
     * @param \Magento\Directory\Model\AllowedCountries $subject
     * @param string $scope
     * @param string|null $scopeCode
     * @return array
     */
    public function beforeGetAllowedCountries(
        \Magento\Directory\Model\AllowedCountries $subject,
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $scopeCode = null
    ) {
        if (in_array($this->customerSession->getCustomer()->getEmail(), $this->config->getOrderTypeChoosingEmails())) {
            $scopeCode = array_map(function (WebsiteInterface $website) {
                return $website->getId();
            }, $this->storeManager->getWebsites());
            $scope = ScopeInterface::SCOPE_WEBSITES;
        }

        return [$scope, $scopeCode];
    }
}
