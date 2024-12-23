<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\Cart;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;
use MageWorx\MultiFees\Model\FeeCollectionValidationManager;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;

class ProductFeeData extends \MageWorx\MultiFees\Block\Checkout\AbstractFeeData
{
    /**
     * @var \MageWorx\MultiFees\Api\FeeCollectionManagerInterface
     */
    protected $feeCollectionManager;

    /**
     * ProductFeeData constructor.
     *
     * @param QuoteFeeManagerInterface $quoteFeeManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param FeeCollectionValidationManager $feeCollectionValidationManager
     * @param FeeCollectionManagerInterface $feeCollectionManager
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
        \MageWorx\MultiFees\Api\FeeCollectionManagerInterface $feeCollectionManager,
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
        $this->feeCollectionManager = $feeCollectionManager;
    }

    /**
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMultifees(): AbstractCollection
    {
        return $this->feeCollectionManager->getProductFeeCollection(
            false,
            false,
            FeeCollectionManagerInterface::HIDDEN_MODE_EXCLUDE
        );
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFeeData(): array
    {
        $feeData        = parent::getFeeData();
        $feeData['url'] = $this->getUrl('multifees/checkout/productFee');
        $currentItem    = $this->helperData->getCurrentItem();

        if ($currentItem) {
            $feeData['quote_item_id'] = $currentItem->getItemId();
        }

        return $feeData;
    }
}
