<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\PayPal\Express;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;
use MageWorx\MultiFees\Model\FeeCollectionValidationManager;

/**
 * Class AbstractFee
 */
abstract class AbstractFee extends \MageWorx\MultiFees\Block\Checkout\AbstractFeeData
{
    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

    /**
     * AbstractFee constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param FeeCollectionValidationManager $feeCollectionValidationManager
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        QuoteFeeManagerInterface $quoteFeeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Customer\Model\Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Session\SessionManager $sessionManager,
        FeeCollectionValidationManager $feeCollectionValidationManager,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct(
            $quoteFeeManager,
            $context,
            $helperData,
            $helperPrice,
            $checkoutSession,
            $currency,
            $customerSession,
            $priceCurrency,
            $sessionManager,
            $feeCollectionValidationManager,
            $data
        );

        $this->configProvider = $configProvider;

        if (!empty($layoutProcessors)) {
            $this->layoutProcessors = $layoutProcessors;
        } elseif (isset($data['layoutProcessors'])) {
            $this->layoutProcessors = $data['layoutProcessors'];
            unset($data['layoutProcessors']);
        }
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->helperData->isEnable() ?: false;
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     */
    public function getCheckoutConfig(): array
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout(): string
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return $this->helperData->serializeValue($this->jsLayout);
    }

    /**
     * Get base url for block.
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
