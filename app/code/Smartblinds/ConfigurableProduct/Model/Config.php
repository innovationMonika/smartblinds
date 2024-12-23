<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private RequestInterface $request;


    public function __construct(ScopeConfigInterface $scopeConfig, RequestInterface $request)
    {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    public function getConfiguratorTitle(): string
    {
        return (string) $this->getValue('configurator/title');
    }

    public function getConfiguratorDescription(): string
    {
        return (string) $this->getValue('configurator/description');
    }

    public function getConfiguratorTips(): array
    {
        if ($this->request->getFullActionName() === 'checkout_cart_index') {
             return array_filter(explode(PHP_EOL, $this->getValue('configurator/carttips') ?? ''));
        }
        else if($this->request->getFullActionName() === 'checkout_index_index'){
             return array_filter(explode(PHP_EOL, $this->getValue('configurator/checkouttips') ?? ''));
        }

        return array_filter(explode(PHP_EOL, $this->getValue('configurator/tips') ?? ''));
    }

    public function getDiscountMessage(): string
    {
        return (string) $this->getValue('messages/discount');
    }

    public function getMeasurementMessage(): string
    {
        return (string) $this->getValue('messages/measurement');
    }

    public function getAdditionalImages(): array
    {
        return array_values(
            array_filter(
                explode(PHP_EOL, $this->getValue('configurator/additional_images') ?? '')
            )
        );
    }

    private function getValue(string $value): ?string
    {
        return $this->scopeConfig->getValue($this->getPath($value), ScopeInterface::SCOPE_STORE);
    }

    private function getPath(string $field): string
    {
        return "smartblinds_configurable_product/$field";
    }
}
