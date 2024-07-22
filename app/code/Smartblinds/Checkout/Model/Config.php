<?php

namespace Smartblinds\Checkout\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private CustomerCollectionFactory $customerCollectionFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    public function getOrderTypeChoosingEmails(): array
    {
        return array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(PHP_EOL, $this->getValue('general/order_type_choosing_emails') ?? '')
                )
            )
        );
    }

    public function getSupportCustomerIds(): array
    {
        $customerCollection = $this->customerCollectionFactory->create();
        return array_map(function ($customer) {
            return $customer->getId();
        }, $customerCollection->getItems());
    }

    private function getValue(string $value): ?string
    {
        return $this->scopeConfig->getValue($this->getPath($value), ScopeInterface::SCOPE_STORE);
    }

    private function getPath(string $field): string
    {
        return "smartblinds_checkout/$field";
    }
}
