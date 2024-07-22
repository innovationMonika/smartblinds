<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Api;

use MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollection;

interface FeeCollectionManagerInterface
{
    const HIDDEN_MODE_ALL     = 0;
    const HIDDEN_MODE_ONLY    = 1;
    const HIDDEN_MODE_EXCLUDE = 2;

    /**
     * Get collection of the available cart fees for the current quote address
     *
     * @important Returns loaded collection
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
    ): CartFeeCollection;

    /**
     * Get only required cart fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\CartFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredCartFees(): array;

    /**
     * Get collection of the available shipping fees for the current quote address
     *
     * @important Returns loaded collection
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
    ): ShippingFeeCollection;

    /**
     * Get only required shipping fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\ShippingFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredShippingFees(): array;

    /**
     * Get collection of the available payment fees for the current quote address
     *
     * @important Returns loaded collection
     *
     * @param bool $required
     * @param bool $isDefault
     * @param int $hiddenMode
     * @return PaymentFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPaymentFeeCollection(
        bool $required = false,
        bool $isDefault = false,
        int $hiddenMode = self::HIDDEN_MODE_ALL
    ): PaymentFeeCollection;

    /**
     * Get only required payment fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\PaymentFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredPaymentFees(): array;

    /**
     * Get collection of the available product fees for the current quote address
     *
     * @important Returns loaded collection
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
    ): ProductFeeCollection;

    /**
     * Get only required cart fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\ProductFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredProductFees(): array;

    /**
     * Set quote for the future validation
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return FeeCollectionManagerInterface
     */
    public function setQuote(\Magento\Quote\Api\Data\CartInterface $quote): FeeCollectionManagerInterface;

    /**
     * Get currently stored quote which used for the fee validation
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote(): \Magento\Quote\Api\Data\CartInterface;

    /**
     * Set quote address for which fees should be valid before manager return them
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return FeeCollectionManagerInterface
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $address): FeeCollectionManagerInterface;

    /**
     * Get currently stored quote address which used for the fee validation
     *
     * @param string $type \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getAddress(string $type = \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
    ): \Magento\Quote\Api\Data\AddressInterface;

    /**
     * Remove cached data
     *
     * @return FeeCollectionManagerInterface
     */
    public function clean(): FeeCollectionManagerInterface;
}
