<?php

namespace Smartblinds\Checkout\Plugin\Block\Checkout\LayoutProcessor;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\ArrayManager;
use Smartblinds\Checkout\Model\Config;
use Smartblinds\Checkout\Model\Source\OrderTypes;

class AddSupportFields
{
    private ArrayManager $arrayManager;
    private Session $customerSession;
    private OrderTypes $orderTypes;
    private Config $config;

    public function __construct(
        ArrayManager $arrayManager,
        Session $customerSession,
        OrderTypes $orderTypes,
        Config $config
    ) {
        $this->arrayManager = $arrayManager;
        $this->customerSession = $customerSession;
        $this->orderTypes = $orderTypes;
        $this->config = $config;
    }

    public function afterProcess(
        LayoutProcessor $subject,
        $result
    ) {
        if (!$this->customerSession->isLoggedIn()) {
            return $result;
        }
        $customerEmail = $this->customerSession->getCustomer()->getEmail();
        if (!in_array($customerEmail, $this->config->getOrderTypeChoosingEmails())) {
            return $result;
        }
        $pathParts = [
            'components',
            'checkout',
            'children',
            'steps',
            'children',
            'shipping-step',
            'children',
            'shippingAddress',
            'children',
            'before-form',
            'children'
        ];
        $path = implode('/', $pathParts);
        return $this->arrayManager->merge($path, $result, $this->getFieldsConfig());
    }

    public function getFieldsConfig(): array
    {
        return [
            'order_type' => [
                'component' => 'Magento_Ui/js/form/element/select',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'Smartblinds_Checkout/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'id' => 'order_type'
                ],
                'dataScope' => 'shippingAddress.order_type',
                'label' => __('Order Type'),
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => 1,
                'id' => 'order_type',
                'options' => $this->orderTypes->toOptionArray()
            ],
            'base_increment_id' => [
                'component' => 'Smartblinds_Checkout/js/form/element/base-increment-id',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'Smartblinds_Checkout/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'id' => 'base_increment_id'
                ],
                'dataScope' => 'shippingAddress.base_increment_id',
                'label' => __('Original Increment Id'),
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => 2,
                'id' => 'base_increment_id'
            ]
        ];
    }
}
