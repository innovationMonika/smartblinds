<?php

namespace Smartblinds\Catalog\Helper;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Amount extends AbstractHelper
{
    private StoreManagerInterface $storeManager;
    private CurrencyFactory $currencyFactory;
    private LoggerInterface $logger;
    private Http $http;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        LoggerInterface $logger,
        Http $http
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
        $this->http = $http;
        parent::__construct($context);
    }

    public function formatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $isCategoryOrSearchPage = in_array($this->http->getFullActionName(), [
            'catalog_category_view',
            'catalogsearch_result_index'
        ]);
        $options = $isCategoryOrSearchPage ? ['display' => \Zend_Currency::NO_SYMBOL] : [];
        return $this->getCurrency($scope, $currency)
            ->formatPrecision($amount, $precision, $options, $includeContainer);
    }

        public function formatCurrencyWithoutCode(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $isCategoryOrSearchPage = in_array($this->http->getFullActionName(), [
            'catalog_category_view',
            'catalogsearch_result_index'
        ]);
        $options = ['display' => \Zend_Currency::NO_SYMBOL];
        return $this->getCurrency($scope, $currency)
            ->formatPrecision($amount, $precision, $options, $includeContainer);
    }

    private function getCurrency($scope = null, $currency = null)
    {
        if ($currency instanceof Currency) {
            $currentCurrency = $currency;
        } elseif (is_string($currency)) {
            $currency = $this->currencyFactory->create()
                ->load($currency);
            $baseCurrency = $this->getStore($scope)
                ->getBaseCurrency();
            $currentCurrency = $baseCurrency->getRate($currency) ? $currency : $baseCurrency;
        } else {
            $currentCurrency = $this->getStore($scope)
                ->getCurrentCurrency();
        }

        return $currentCurrency;
    }

    private function getStore($scope = null)
    {
        try {
            if (!$scope instanceof Store) {
                $scope = $this->storeManager->getStore($scope);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $scope = $this->storeManager->getStore();
        }

        return $scope;
    }
}
