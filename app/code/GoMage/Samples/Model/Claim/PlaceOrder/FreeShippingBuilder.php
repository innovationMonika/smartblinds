<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Magento\Store\Model\ScopeInterface;

class FreeShippingBuilder
{
    private MethodFactory $rateMethodFactory;
    private ScopeConfigInterface $scopeConfig;
    private RateFactory $rateFactory;

    public function __construct(
        MethodFactory $rateMethodFactory,
        ScopeConfigInterface $scopeConfig,
        RateFactory $rateFactory
    ) {
        $this->rateMethodFactory = $rateMethodFactory;
        $this->scopeConfig = $scopeConfig;
        $this->rateFactory = $rateFactory;
    }

    public function build(int $storeId)
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier('freeshipping');
        $method->setCarrierTitle($this->getConfigData($storeId, 'title'));
        $method->setMethod('freeshipping');
        $method->setMethodTitle($this->getConfigData($storeId, 'name'));
        $method->setPrice(0);
        $method->setCost(0);
        return $this->rateFactory->create()->importShippingRate($method);
    }

    private function getConfigData(int $storeId, $field)
    {
        $path = "carriers/freeshipping/$field";
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
