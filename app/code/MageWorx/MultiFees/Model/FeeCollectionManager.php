<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection;

class FeeCollectionManager implements FeeCollectionManagerInterface
{
    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection
     */
    protected $loadedCartFeeCollection;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection
     */
    protected $loadedShippingFeeCollection;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection
     */
    protected $loadedPaymentFeeCollection;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollection
     */
    protected $loadedProductFeeCollection;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollectionFactory
     */
    protected $cartFeeCollectionFactory;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollectionFactory
     */
    protected $shippingFeeCollectionFactory;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollectionFactory
     */
    protected $paymentFeeCollectionFactory;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollectionFactory
     */
    protected $productFeeCollectionFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterface[]|\Magento\Quote\Model\Quote\Address[]
     */
    protected $address = [];

    /**
     * ValidateQuoteTotals constructor.
     *
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param ResourceModel\Fee\CartFeeCollectionFactory $cartFeeCollectionFactory
     * @param ResourceModel\Fee\ShippingFeeCollectionFactory $shippingFeeCollectionFactory
     * @param ResourceModel\Fee\PaymentFeeCollectionFactory $paymentFeeCollectionFactory
     * @param ResourceModel\Fee\ProductFeeCollectionFactory $productFeeCollectionFactory
     */
    public function __construct(
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollectionFactory $cartFeeCollectionFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollectionFactory $shippingFeeCollectionFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollectionFactory $paymentFeeCollectionFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollectionFactory $productFeeCollectionFactory
    ) {
        $this->helperData                   = $helperData;
        $this->cartFeeCollectionFactory     = $cartFeeCollectionFactory;
        $this->shippingFeeCollectionFactory = $shippingFeeCollectionFactory;
        $this->paymentFeeCollectionFactory  = $paymentFeeCollectionFactory;
        $this->productFeeCollectionFactory  = $productFeeCollectionFactory;
    }

    /**
     * Get collection of the available cart fees for the current quote address
     *
     * @param bool $required
     * @param bool $isDefault
     * @param int $hiddenMode
     * @return CartFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCartFeeCollection(
        bool $required = false,
        bool $isDefault = false,
        int $hiddenMode = self::HIDDEN_MODE_ALL
    ): CartFeeCollection {
        $key = 'required' . (int)$required . 'default' . (int)$isDefault . 'hiddenMode' . $hiddenMode;
        if (!empty($this->loadedCartFeeCollection[$key])) {
            return $this->loadedCartFeeCollection[$key];
        }

        // Get multifees
        $quote   = $this->getQuote();
        $address = $this->getAddress(\Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING);
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection $feeCollection */
        $feeCollection = $this->cartFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $quote->getCustomerGroupId()
            )
            ->addRequiredFilter($required)
            ->addIsDefaultFilter($isDefault)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        if ($hiddenMode === self::HIDDEN_MODE_ONLY) {
            $feeCollection->addFieldToFilter('input_type', FeeInterface::FEE_INPUT_TYPE_HIDDEN);
        } elseif ($hiddenMode === self::HIDDEN_MODE_EXCLUDE) {
            $feeCollection->addFieldToFilter('input_type', ['nin' => FeeInterface::FEE_INPUT_TYPE_HIDDEN]);
        }

        /**
         * @var \MageWorx\MultiFees\Model\CartFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->canProcessFee($address, $quote)) {
                $feeCollection->removeItemByKey($key);
            }

            $fee->setStoreId($quote->getStoreId());
        }

        $this->loadedCartFeeCollection[$key] = $feeCollection;

        return $this->loadedCartFeeCollection[$key];
    }

    /**
     * Get collection of the available product fees for the current quote address and product
     *
     * @param bool $required
     * @param bool $isDefault
     * @param int $hiddenMode
     * @return ProductFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductFeeCollection(
        bool $required = false,
        bool $isDefault = false,
        int $hiddenMode = self::HIDDEN_MODE_ALL
    ): ProductFeeCollection {
        $key = 'required' . (int)$required . 'default' . (int)$isDefault . 'hiddenMode' . $hiddenMode;
        if (!empty($this->loadedProductFeeCollection[$key])) {
            return $this->loadedProductFeeCollection[$key];
        }

        // Get multifees
        $quote = $this->getQuote();
        /** @var ProductFeeCollection $feeCollection */
        $feeCollection = $this->productFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $quote->getCustomerGroupId()
            )
            ->addRequiredFilter($required)
            ->addIsDefaultFilter($isDefault)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        if ($hiddenMode === self::HIDDEN_MODE_ONLY) {
            $feeCollection->addFieldToFilter('input_type', FeeInterface::FEE_INPUT_TYPE_HIDDEN);
        } elseif ($hiddenMode === self::HIDDEN_MODE_EXCLUDE) {
            $feeCollection->addFieldToFilter('input_type', ['nin' => FeeInterface::FEE_INPUT_TYPE_HIDDEN]);
        }

        /**
         * @var \MageWorx\MultiFees\Model\CartFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            $fee->setStoreId($quote->getStoreId());
        }

        $this->loadedProductFeeCollection[$key] = $feeCollection;

        return $this->loadedProductFeeCollection[$key];
    }

    /**
     * Get collection of the available shipping fees for the current quote address
     *
     * @param bool $required
     * @param bool $isDefault
     * @param int $hiddenMode
     * @return ShippingFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShippingFeeCollection(
        bool $required = false,
        bool $isDefault = false,
        int $hiddenMode = self::HIDDEN_MODE_ALL
    ): ShippingFeeCollection {
        $key = 'required' . (int)$required . 'default' . (int)$isDefault . 'hiddenMode' . $hiddenMode;
        if (!empty($this->loadedShippingFeeCollection[$key])) {
            return $this->loadedShippingFeeCollection[$key];
        }

        // Get multifees
        $quote   = $this->getQuote();
        $address = $this->getAddress(\Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING);
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection $feeCollection */
        $feeCollection = $this->shippingFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $quote->getCustomerGroupId()
            )
            ->addRequiredFilter($required)
            ->addIsDefaultFilter($isDefault)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        if ($hiddenMode === self::HIDDEN_MODE_ONLY) {
            $feeCollection->addFieldToFilter('input_type', FeeInterface::FEE_INPUT_TYPE_HIDDEN);
        } elseif ($hiddenMode === self::HIDDEN_MODE_EXCLUDE) {
            $feeCollection->addFieldToFilter('input_type', ['nin' => FeeInterface::FEE_INPUT_TYPE_HIDDEN]);
        }

        /**
         * @var \MageWorx\MultiFees\Model\ShippingFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            $shippingMethods = $fee->getShippingMethods();
            if (!empty($shippingMethods) && !in_array($address->getShippingMethod(), $shippingMethods)) {
                $feeCollection->removeItemByKey($key);
                continue;
            }

            if (!$fee->canProcessFee($address, $quote)) {
                $feeCollection->removeItemByKey($key);
                continue;
            }

            $fee->setStoreId($quote->getStoreId());
        }
        // Get multifees end

        $this->loadedShippingFeeCollection[$key] = $feeCollection;

        return $this->loadedShippingFeeCollection[$key];
    }

    /**
     * Get collection of the available payment fees for the current quote address
     *
     * @param bool $required
     * @param bool $isDefault
     * @param int $hiddenMode
     * @return PaymentFeeCollection
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPaymentFeeCollection(
        bool $required = false,
        bool $isDefault = false,
        int $hiddenMode = self::HIDDEN_MODE_ALL
    ): PaymentFeeCollection {
        $key = 'required' . (int)$required . 'default' . (int)$isDefault . 'hiddenMode' . $hiddenMode;
        if (!empty($this->loadedPaymentFeeCollection[$key])) {
            return $this->loadedPaymentFeeCollection[$key];
        }

        // Get multifees
        $quote   = $this->getQuote();
        $address = $this->getAddress(\Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_BILLING);
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection $feeCollection */
        $feeCollection = $this->paymentFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $quote->getCustomerGroupId()
            )
            ->addRequiredFilter($required)
            ->addIsDefaultFilter($isDefault)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        if ($hiddenMode === self::HIDDEN_MODE_ONLY) {
            $feeCollection->addFieldToFilter('input_type', FeeInterface::FEE_INPUT_TYPE_HIDDEN);
        } elseif ($hiddenMode === self::HIDDEN_MODE_EXCLUDE) {
            $feeCollection->addFieldToFilter('input_type', ['nin' => FeeInterface::FEE_INPUT_TYPE_HIDDEN]);
        }

        $paymentMethod = $quote->getPayment()->getMethod();
        /**
         * @var \MageWorx\MultiFees\Model\PaymentFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            $paymentMethods = $fee->getPaymentMethods();

            if (!empty($fee->getPaymentMethods()) && !in_array($paymentMethod, $paymentMethods)) {
                $feeCollection->removeItemByKey($key);
                continue;
            }

            if (!$fee->canProcessFee($address, $quote)) {
                $feeCollection->removeItemByKey($key);
                continue;
            }

            $fee->setStoreId($quote->getStoreId());
        }

        // Get multifees end

        $this->loadedPaymentFeeCollection[$key] = $feeCollection;

        return $this->loadedPaymentFeeCollection[$key];
    }

    /**
     * Get only required cart fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\CartFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredCartFees(): array
    {
        $loadedCollection = $this->getCartFeeCollection(true);
        $items            = $loadedCollection->getItems();

        return $items;
    }

    /**
     * Get only required cart fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\ProductFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredProductFees(): array
    {
        $loadedCollection = $this->getProductFeeCollection(true);
        $items            = $loadedCollection->getItems();

        return $items;
    }

    /**
     * Get only required shipping fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\ShippingFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredShippingFees(): array
    {
        $loadedCollection = $this->getShippingFeeCollection(true);
        $items            = $loadedCollection->getItems();

        return $items;
    }

    /**
     * Get only required payment fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\PaymentFee[]
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredPaymentFees(): array
    {
        $loadedCollection = $this->getPaymentFeeCollection(true);
        $items            = $loadedCollection->getItems();

        return $items;
    }

    /**
     * Set quote for the future validation
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return FeeCollectionManagerInterface
     */
    public function setQuote(\Magento\Quote\Api\Data\CartInterface $quote): FeeCollectionManagerInterface
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get currently stored quote which used for the fee validation.
     * Loads the quote using the fee helper in case the quote does not set yet.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote(): \Magento\Quote\Api\Data\CartInterface
    {
        if ($this->quote) {
            return $this->quote;
        }

        $this->quote = $this->helperData->getQuote();

        return $this->quote;
    }

    /**
     * Set quote address for which fees should be valid before manager return them
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return FeeCollectionManagerInterface
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $address): FeeCollectionManagerInterface
    {
        if ($address->getAddressType()) {
            $this->address[$address->getAddressType()] = $address;
        }

        return $this;
    }

    /**
     * Get currently stored quote address which used for the fee validation
     *
     * @param string $type
     * @return \Magento\Quote\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddress(string $type = \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
    ): \Magento\Quote\Api\Data\AddressInterface {
        if (!empty($this->address[$type])) {
            return $this->address[$type];
        }

        $quote                = $this->getQuote();
        $this->address[$type] = $this->helperData->getSalesAddress($quote, $type);

        return $this->address[$type];
    }

    /**
     * Remove cached data
     *
     * @return FeeCollectionManagerInterface
     */
    public function clean(): FeeCollectionManagerInterface
    {
        $this->loadedCartFeeCollection     = null;
        $this->loadedShippingFeeCollection = null;
        $this->loadedPaymentFeeCollection  = null;
        $this->quote                       = null;
        $this->address                     = [];

        return $this;
    }
}
